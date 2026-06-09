<?php

namespace App\Http\Controllers;

use App\Models\RideSharingLocation;
use App\Models\RideSharingSchedule;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RideSharingController extends Controller
{
    /**
     * Show the Ride Sharing booking page, pre-filled with hero-form search params.
     */
    public function index(Request $request): Response
    {
        $locations = RideSharingLocation::active()->get(['id', 'name', 'area']);
        $schedules = RideSharingSchedule::active()->get(['id', 'departure_time', 'label']);

        return Inertia::render('RideSharing/Index', [
            'locations' => $locations,
            'schedules' => $schedules,
            'search' => [
                'date' => $request->query('date', ''),
                'pickup_location_id' => $request->query('pickup_location_id', ''),
                'dropoff_location_id' => $request->query('dropoff_location_id', ''),
                'schedule_id' => $request->query('schedule_id', ''),
                'passengers' => (int) $request->query('passengers', 1),
            ],
        ]);
    }
}
