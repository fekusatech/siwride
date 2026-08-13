<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DriverService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DriverServiceController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');

        $services = DriverService::with('driver')
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhereHas('driver', fn ($q) => $q->where('firstname', 'like', "%{$search}%")
                            ->orWhere('lastname', 'like', "%{$search}%"));
                });
            })
            ->when($status, fn ($q, $s) => $q->where('status', $s))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/DriverServices/Index', [
            'services' => $services,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function show(DriverService $driverService)
    {
        $driverService->load('driver');

        return Inertia::render('Admin/DriverServices/Show', [
            'service' => $driverService,
        ]);
    }

    public function updateStatus(Request $request, DriverService $driverService)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,approved,rejected'],
            'rejection_reason' => ['nullable', 'string', 'max:500'],
            'is_featured' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ]);

        $validated['is_featured'] = $validated['is_featured'] ?? $driverService->is_featured;
        $validated['sort_order'] = $validated['sort_order'] ?? $driverService->sort_order;

        if ($validated['status'] === DriverService::STATUS_APPROVED) {
            $validated['published_at'] ??= $driverService->published_at ?? now();
            $validated['rejection_reason'] = null;
        } elseif ($validated['status'] === DriverService::STATUS_REJECTED) {
            $validated['published_at'] = null;
            $validated['is_featured'] = false;
        } else {
            $validated['published_at'] = null;
            $validated['rejection_reason'] = null;
            $validated['is_featured'] = false;
        }

        $driverService->update($validated);

        return back()->with('success', 'Service status updated.');
    }
}
