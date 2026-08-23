<?php

namespace App\Http\Controllers;

use App\Models\DriverService;
use App\Models\DriverServiceBooking;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DriverProfileController extends Controller
{
    public function show(Request $request, User $user): Response
    {
        abort_unless($user->isDriver(), 404);

        $services = DriverService::query()
            ->where('driver_id', $user->id)
            ->where('status', DriverService::STATUS_APPROVED)
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (DriverService $s) => [
                'slug' => $s->slug,
                'title' => $s->title,
                'description' => $s->description,
                'image_url' => $s->image_url,
                'price_per_pax' => $s->price_per_pax ? (float) $s->price_per_pax : null,
                'duration_label' => $s->duration_label,
                'min_pax' => $s->min_pax,
                'max_pax' => $s->max_pax,
                'is_featured' => $s->is_featured,
                'url' => "/services/{$s->slug}",
            ]);

        $completedBookings = DriverServiceBooking::query()
            ->whereHas('driverService', fn ($q) => $q->where('driver_id', $user->id))
            ->where('status', DriverServiceBooking::STATUS_COMPLETED)
            ->count();

        return Inertia::render('customer/driver-profile', [
            'driver' => [
                'id' => $user->id,
                'name' => $user->name,
                'image' => $user->image ? asset('storage/'.$user->image) : null,
                'joined_at' => $user->created_at?->format('M Y'),
                'total_services' => $services->count(),
                'completed_bookings' => $completedBookings,
            ],
            'services' => $services,
        ]);
    }
}
