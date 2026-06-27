<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DriverController extends Controller
{
    private function getDriver(Request $request): ?Driver
    {
        return Driver::where('email', $request->user()->email)->first();
    }

    public function dashboard(Request $request)
    {
        $driver = $this->getDriver($request);
        if (! $driver) {
            return response()->json(['status' => 'error', 'message' => 'Driver not found'], 404);
        }

        $today = now()->toDateString();

        $todayJobs = Order::where('driver_id', $driver->id)
            ->whereDate('date', $today)
            ->where('is_cancelled', false);

        $todayCompleted = (clone $todayJobs)->where('status', 'selesai')->count();
        $todayEarnings = (float) (clone $todayJobs)->where('status', 'selesai')->sum('price');
        $todayPending = (clone $todayJobs)->whereIn('status', ['pending', 'otw', 'tiba'])->count();
        $todayTotal = (clone $todayJobs)->count();

        $monthStart = now()->startOfMonth()->toDateString();
        $monthEarnings = (float) Order::where('driver_id', $driver->id)
            ->whereBetween('date', [$monthStart, $today])
            ->where('status', 'selesai')
            ->where('is_cancelled', false)
            ->sum('price');

        $weekStart = now()->startOfWeek()->toDateString();
        $weekEarnings = (float) Order::where('driver_id', $driver->id)
            ->whereBetween('date', [$weekStart, $today])
            ->where('status', 'selesai')
            ->where('is_cancelled', false)
            ->sum('price');

        $totalJobs = Order::where('driver_id', $driver->id)
            ->where('is_cancelled', false)
            ->count();

        $totalCompleted = Order::where('driver_id', $driver->id)
            ->where('status', 'selesai')
            ->where('is_cancelled', false)
            ->count();

        return response()->json([
            'status' => 'success',
            'data' => [
                'today' => [
                    'total_jobs' => $todayTotal,
                    'completed' => $todayCompleted,
                    'pending' => $todayPending,
                    'earnings' => $todayEarnings,
                ],
                'this_week' => [
                    'earnings' => $weekEarnings,
                ],
                'this_month' => [
                    'earnings' => $monthEarnings,
                ],
                'all_time' => [
                    'total_jobs' => $totalJobs,
                    'completed' => $totalCompleted,
                ],
            ],
        ]);
    }

    public function vehicle(Request $request)
    {
        $driver = $this->getDriver($request);
        if (! $driver) {
            return response()->json(['status' => 'error', 'message' => 'Driver not found'], 404);
        }

        $vehicle = $driver->vehicles()->first();

        return response()->json([
            'status' => 'success',
            'data' => $vehicle,
        ]);
    }

    public function updateVehicle(Request $request)
    {
        $driver = $this->getDriver($request);
        if (! $driver) {
            return response()->json(['status' => 'error', 'message' => 'Driver not found'], 404);
        }

        $validated = $request->validate([
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'type' => 'nullable|string|max:255',
            'registration_number' => 'required|string|max:255',
            'color' => 'nullable|string|max:255',
        ]);

        $vehicle = $driver->vehicles()->first();

        if ($vehicle) {
            $vehicle->update($validated);
        } else {
            $vehicle = $driver->vehicles()->create($validated);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Vehicle info updated successfully.',
            'data' => $vehicle,
        ]);
    }

    public function updateAvailability(Request $request)
    {
        $request->validate([
            'is_online' => 'required|boolean',
        ]);

        $driver = $this->getDriver($request);
        if (! $driver) {
            return response()->json(['status' => 'error', 'message' => 'Driver not found'], 404);
        }

        $driver->update(['status' => $request->is_online ? 'active' : 'inactive']);

        return response()->json([
            'status' => 'success',
            'message' => $request->is_online ? 'You are now online.' : 'You are now offline.',
            'data' => [
                'is_online' => $request->is_online,
            ],
        ]);
    }

    public function completeJob(Request $request, $id)
    {
        $driver = $this->getDriver($request);
        if (! $driver) {
            return response()->json(['status' => 'error', 'message' => 'Driver not found'], 404);
        }

        $validated = $request->validate([
            'payment_method' => 'nullable|in:cash,xendit,transfer',
            'customer_phone' => 'nullable|string|max:20',
        ]);

        $order = Order::where('id', $id)
            ->where('driver_id', $driver->id)
            ->firstOrFail();

        if ($order->status === 'selesai') {
            return response()->json([
                'status' => 'error',
                'message' => 'This job is already completed.',
            ], 400);
        }

        return DB::transaction(function () use ($order, $validated) {
            $paymentMethod = $validated['payment_method'] ?? ($order->is_cash ? 'cash' : 'xendit');

            $order->update([
                'status' => 'selesai',
                'payment_method' => $paymentMethod,
                'payment_status' => $paymentMethod === 'cash' ? 'paid' : 'pending',
            ]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'driver_id' => $order->driver_id,
                'status' => 'selesai',
                'notes' => 'Job completed via driver app. Payment: '.$paymentMethod,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Job completed successfully.',
                'data' => [
                    'order_id' => $order->id,
                    'status' => 'selesai',
                    'payment_method' => $paymentMethod,
                    'payment_status' => $paymentMethod === 'cash' ? 'paid' : 'pending',
                    'total' => (float) $order->price,
                ],
            ]);
        });
    }
}
