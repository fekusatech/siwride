<?php

namespace App\Http\Controllers;

use App\Models\RideSharingCity;
use App\Models\RideSharingRoute;
use Carbon\Carbon;
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
                ->with(['paths', 'schedules' => function ($q) use ($request, $pickup_id, $dropoff_id) {
                    $q->where('is_active', true)->with(['vehicleCategory', 'prices' => function ($p) use ($pickup_id, $dropoff_id) {
                        $p->where('from_city_id', $pickup_id)
                            ->where('to_city_id', $dropoff_id)
                            ->where('is_active', true);
                    }]);
                    if ($date = $request->query('date')) {
                        $dayOfWeek = Carbon::parse($date)->englishDayOfWeek; // e.g. "Sunday"
                        $q->whereJsonContains('days', $dayOfWeek);
                    }
                }])
                ->get()
                ->filter(function ($route) use ($pickup_id, $dropoff_id) {
                    $pickupSeq = $route->paths->firstWhere('city_id', $pickup_id)->sequence ?? 9999;
                    $dropoffSeq = $route->paths->firstWhere('city_id', $dropoff_id)->sequence ?? 0;

                    // Keep route if it's correct direction and at least one schedule has a valid price
                    return $pickupSeq < $dropoffSeq && $route->schedules->filter(function ($s) {
                        return $s->prices->isNotEmpty();
                    })->isNotEmpty();
                })
                ->map(function ($route) use ($pickup_id, $dropoff_id) {

                    // Sort schedules in PHP since it's a JSON column mapping
                    $sortedSchedules = $route->schedules->filter(function ($schedule) {
                        // Only return schedules that have a price for this specific city pair
                        return $schedule->prices->isNotEmpty();
                    })->sortBy(function ($schedule) use ($pickup_id) {
                        return $schedule->departure_times[$pickup_id] ?? '23:59';
                    })->values()->map(function ($schedule) use ($pickup_id, $dropoff_id) {
                        $pickupTime = $schedule->departure_times[$pickup_id] ?? null;
                        $dropoffTime = $schedule->departure_times[$dropoff_id] ?? null;

                        $estimatedMinutes = null;
                        if ($pickupTime && $dropoffTime) {
                            $pickupCarbon = Carbon::parse($pickupTime);
                            $dropoffCarbon = Carbon::parse($dropoffTime);
                            if ($dropoffCarbon->lessThan($pickupCarbon)) {
                                $dropoffCarbon->addDay();
                            }
                            $estimatedMinutes = $pickupCarbon->diffInMinutes($dropoffCarbon);
                        }

                        // Get the exact price object for this schedule
                        $priceObj = $schedule->prices->first();

                        // Include specific departure time for this pickup point
                        $schedule->specific_departure_time = $pickupTime;
                        $schedule->specific_arrival_time = $dropoffTime;
                        $schedule->estimated_minutes = $estimatedMinutes;
                        $schedule->price = $priceObj->price; // Set price directly on schedule

                        return $schedule;
                    });

                    return [
                        'id' => $route->id,
                        'name' => $route->name,
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
