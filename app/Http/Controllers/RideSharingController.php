<?php

namespace App\Http\Controllers;

use App\Models\RideSharingCity;
use App\Models\RideSharingRoute;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RideSharingController extends Controller
{
    /**
     * Show the Ride Sharing booking page, and fetch available routes if from/to are specified.
     */
    public function index(Request $request): Response
    {
        $locations = RideSharingCity::orderBy('name')->get(['id', 'name', 'address']);

        $pickup_id = $request->query('pickup_location_id');
        $dropoff_id = $request->query('dropoff_location_id');

        $availableRoutes = [];

        if ($pickup_id && $dropoff_id && $pickup_id != $dropoff_id) {
            $availableRoutes = RideSharingRoute::where('is_active', true)
                ->whereHas('paths', function ($q) use ($pickup_id) {
                    $q->where('city_id', $pickup_id);
                })
                ->whereHas('paths', function ($q) use ($dropoff_id) {
                    $q->where('city_id', $dropoff_id);
                })
                ->with(['paths', 'schedules' => function ($q) use ($request) {
                    $q->where('is_active', true)->with('vehicleCategory');
                    if ($date = $request->query('date')) {
                        $dayOfWeek = \Carbon\Carbon::parse($date)->englishDayOfWeek; // e.g. "Sunday"
                        $q->whereJsonContains('days', $dayOfWeek);
                    }
                }, 'prices' => function ($q) use ($pickup_id, $dropoff_id) {
                    $q->where('from_city_id', $pickup_id)
                        ->where('to_city_id', $dropoff_id);
                }])
                ->get()
                ->filter(function ($route) use ($pickup_id, $dropoff_id) {
                    $pickupSeq = $route->paths->firstWhere('city_id', $pickup_id)->sequence ?? 9999;
                    $dropoffSeq = $route->paths->firstWhere('city_id', $dropoff_id)->sequence ?? 0;

                    return $pickupSeq < $dropoffSeq && $route->prices->isNotEmpty() && $route->schedules->isNotEmpty();
                })
                ->map(function ($route) use ($pickup_id, $dropoff_id) {
                    $priceObj = $route->prices->first();
                    
                    // Sort schedules in PHP since it's a JSON column mapping
                    $sortedSchedules = $route->schedules->sortBy(function ($schedule) use ($pickup_id) {
                        return $schedule->departure_times[$pickup_id] ?? '23:59';
                    })->values()->map(function ($schedule) use ($pickup_id, $dropoff_id) {
                        $pickupTime = $schedule->departure_times[$pickup_id] ?? null;
                        $dropoffTime = $schedule->departure_times[$dropoff_id] ?? null;
                        
                        $estimatedMinutes = null;
                        if ($pickupTime && $dropoffTime) {
                            $pickupCarbon = \Carbon\Carbon::parse($pickupTime);
                            $dropoffCarbon = \Carbon\Carbon::parse($dropoffTime);
                            if ($dropoffCarbon->lessThan($pickupCarbon)) {
                                $dropoffCarbon->addDay();
                            }
                            $estimatedMinutes = $pickupCarbon->diffInMinutes($dropoffCarbon);
                        }

                        // Include specific departure time for this pickup point
                        $schedule->specific_departure_time = $pickupTime;
                        $schedule->estimated_minutes = $estimatedMinutes;
                        return $schedule;
                    });

                    return [
                        'id' => $route->id,
                        'name' => $route->name,
                        'price' => $priceObj->price,
                        'schedules' => $sortedSchedules,
                    ];
                })
                ->values();
        }

        return Inertia::render('RideSharing/Index', [
            'locations' => $locations,
            'availableRoutes' => $availableRoutes,
            'search' => [
                'date' => $request->query('date', ''),
                'pickup_location_id' => $pickup_id,
                'dropoff_location_id' => $dropoff_id,
                'passengers' => (int) $request->query('passengers', 1),
            ],
        ]);
    }
}
