<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\JobClaim;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JobController extends Controller
{
    private function getDriver(Request $request)
    {
        $user = $request->user();

        return Driver::where('email', $user->email)->first();
    }

    public function shared(Request $request)
    {
        $user = $request->user();

        // Get jobs that are not yet assigned to any driver
        $jobs = Order::whereNull('driver_id')
            ->where('status', 'pending')
            ->latest()
            ->get()
            ->map(function ($job) use ($user) {
                // Logic to hide guest info before 4 PM on the same day
                $isToday = $job->date->isToday();
                $isBefore4PM = now()->hour < 16;
                $job->guest_info_hidden = $isToday && $isBefore4PM;

                if ($job->guest_info_hidden) {
                    $job->customer_name = '***';
                    $job->customer_phone = '***';
                }

                // Add claim information
                $claims = JobClaim::where('order_id', $job->id)->where('status', 'pending')->get();
                $job->is_waiting_approval = $claims->isNotEmpty();
                $job->claimed_by_me = $claims->contains('driver_id', $user->id);

                return $job;
            });

        return response()->json([
            'status' => 'success',
            'data' => $jobs,
        ]);
    }

    public function myRides(Request $request)
    {
        $user = $request->user();

        $jobs = Order::where(function ($query) use ($user) {
            // Order milik saya (Confirmed)
            $query->where('driver_id', $user->id)
                  // ATAU Order yang sedang saya klaim (Waiting Approval)
                ->orWhereHas('claims', function ($q) use ($user) {
                    $q->where('driver_id', $user->id)->where('status', 'pending');
                })
                  // ATAU Order terbuka yang belum ada drivernya sama sekali (Open Order)
                ->orWhere(function ($sq) {
                    $sq->whereNull('driver_id')
                        ->whereDoesntHave('claims')
                        ->where('status', 'pending');
                });
        })
            ->where('status', 'pending') // Hanya ambil yang masih pending/aktif
            ->where('is_cancelled', false)
            ->whereDate('date', '>=', now()->toDateString())
            ->orderBy('date')
            ->orderBy('time')
            ->limit(20)
            ->get()
            ->map(function ($job) use ($user) {
                $job->is_confirmed = ($job->driver_id == $user->id);
                $job->is_waiting_approval = $job->claims()->where('driver_id', $user->id)->exists() && is_null($job->driver_id);
                $job->is_open_order = (is_null($job->driver_id) && ! $job->claims()->exists());

                return $job;
            });

        return response()->json([
            'status' => 'success',
            'data' => $jobs,
        ]);
    }

    public function show(Request $request, $id)
    {
        $job = Order::with(['driver', 'claims.driver'])->findOrFail($id);

        $user = $request->user();

        $isOwner = $job->driver_id == $user->id || $job->claims()->where('driver_id', $user->id)->exists();
        $isAdmin = $user->role === 'admin';
        $isSharedPool = $job->is_shared && is_null($job->driver_id);

        if (! $isOwner && ! $isAdmin && ! $isSharedPool) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to view this job.',
            ], 403);
        }

        $isToday = $job->date->isToday();
        $isBefore4PM = now()->hour < 16;
        $job->guest_info_hidden = $isToday && $isBefore4PM;

        if ($job->guest_info_hidden) {
            $job->customer_name = '***';
            $job->customer_phone = '***';
        }

        return response()->json([
            'status' => 'success',
            'data' => $job,
        ]);
    }

    public function take(Request $request, $id)
    {
        $user = $request->user();

        return DB::transaction(function () use ($id, $user) {
            $order = Order::where('id', $id)->lockForUpdate()->firstOrFail();

            if ($order->driver_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Maaf, pekerjaan ini sudah diambil oleh driver lain.',
                ], 400);
            }

            // Check if already claimed and not rejected
            $existingClaim = JobClaim::where('order_id', $id)
                ->where('driver_id', $user->id)
                ->whereIn('status', ['pending', 'approved'])
                ->first();

            if ($existingClaim) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda sudah mengklaim pekerjaan ini.',
                ], 400);
            }

            // Check daily limit (fallback to 5 if not set)
            $dailyLimit = 5;
            try {
                $limitSetting = Setting::where('setting_key', 'daily_job_limit')->first();
                if ($limitSetting) {
                    $dailyLimit = (int) $limitSetting->setting_value;
                }
            } catch (\Exception $e) {
            }

            $todayJobsCount = Order::where('driver_id', $user->id)
                ->whereDate('date', now()->toDateString())
                ->where('is_cancelled', false)
                ->count();

            if ($todayJobsCount >= $dailyLimit) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Limit harian Anda telah tercapai.',
                ], 400);
            }

            // Record or Update claim in the new table
            JobClaim::updateOrCreate(
                ['order_id' => $id, 'driver_id' => $user->id],
                ['status' => 'pending']
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil mengajukan klaim. Mohon tunggu konfirmasi dari admin.',
            ]);
        });
    }

    public function claim(Request $request, $id)
    {
        return $this->take($request, $id);
    }

    public function acceptClaim(Request $request, $id)
    {
        $request->validate([
            'driver_id' => 'required|exists:users,id',
        ]);

        return DB::transaction(function () use ($id, $request) {
            $order = Order::findOrFail($id);
            $driverId = $request->driver_id;

            // Check if this driver actually claimed this job
            $claim = JobClaim::where('order_id', $id)
                ->where('driver_id', $driverId)
                ->first();

            if (! $claim) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Driver ini belum melakukan klaim pada pekerjaan ini.',
                ], 400);
            }

            // Assign driver to order
            $order->update([
                'driver_id' => $driverId,
                'is_shared' => false,
                'status' => 'pending',
            ]);

            // Update all claims for this order
            JobClaim::where('order_id', $id)->update(['status' => 'rejected']);
            $claim->update(['status' => 'approved']);

            return response()->json([
                'status' => 'success',
                'message' => 'Klaim driver diterima!',
            ]);
        });
    }

    public function rejectClaim(Request $request, $id)
    {
        $request->validate([
            'driver_id' => 'required|exists:users,id',
        ]);

        $claim = JobClaim::where('order_id', $id)
            ->where('driver_id', $request->driver_id)
            ->first();

        if (! $claim) {
            return response()->json([
                'status' => 'error',
                'message' => 'Klaim tidak ditemukan.',
            ], 404);
        }

        $claim->update(['status' => 'rejected']);

        return response()->json([
            'status' => 'success',
            'message' => 'Klaim driver ditolak.',
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $driver = $this->getDriver($request);
        if (! $driver) {
            return response()->json(['status' => 'error', 'message' => 'Driver not found'], 404);
        }

        $request->validate([
            'status' => 'required|in:otw,tiba,selesai',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ]);

        $order = Order::where('id', $id)
            ->where('driver_id', $driver->id)
            ->firstOrFail();

        $order->update([
            'status' => $request->status,
        ]);

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'driver_id' => $driver->id,
            'status' => $request->status,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Status updated successfully.',
            'data' => $order,
        ]);
    }

    public function history(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $user = $request->user();
        $driver = $this->getDriver($request);
        $driverId = $driver ? $driver->id : null;

        if ($order->driver_id != $driverId && $user->role !== 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized.',
            ], 403);
        }

        $history = OrderStatusHistory::where('order_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $history,
        ]);
    }

    public function uploadEvidence(Request $request, $id)
    {
        $driver = $this->getDriver($request);
        if (! $driver) {
            return response()->json(['status' => 'error', 'message' => 'Driver not found'], 404);
        }

        $request->validate([
            'type' => 'required|in:berangkat,tiba',
            'photo' => 'required|image|max:5120',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $order = Order::where('id', $id)
            ->where('driver_id', $driver->id)
            ->firstOrFail();

        $path = $request->file('photo')->store('evidences', 'public');

        $evidence = $order->evidences()->create([
            'type' => $request->type,
            'photo_url' => $path,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'captured_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Evidence uploaded successfully.',
            'data' => $evidence,
        ]);
    }

    // Admin Job Management
    public function indexAll(Request $request)
    {
        $query = Order::with(['driver', 'claims.driver'])->latest();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return response()->json([
            'status' => 'success',
            'data' => $query->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string',
            'customer_phone' => 'required|string',
            'pickup_address' => 'required|string',
            'dropoff_address' => 'required|string',
            'date' => 'required|date',
            'time' => 'required',
            'price' => 'required|numeric',
            'is_cash' => 'boolean',
            'driver_id' => 'nullable|exists:users,id',
        ]);

        $order = Order::create(array_merge($request->all(), [
            'booking_code' => 'SW-'.strtoupper(str()->random(8)),
            'order_number' => '#'.str()->random(6),
            'status' => $request->driver_id ? 'pending' : 'shared',
            'is_shared' => $request->driver_id ? false : true,
        ]));

        return response()->json([
            'status' => 'success',
            'data' => $order,
        ], 201);
    }

    public function assign(Request $request, $id)
    {
        $request->validate([
            'driver_id' => 'required|exists:users,id',
        ]);

        $order = Order::findOrFail($id);
        $order->update([
            'driver_id' => $request->driver_id,
            'status' => 'pending',
            'is_shared' => false,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Job successfully assigned to driver.',
            'data' => $order,
        ]);
    }
}
