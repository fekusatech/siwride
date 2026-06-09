<?php

namespace App\Http\Controllers\Admin\RideSharing;

use App\Http\Controllers\Controller;
use App\Models\RideSharingSchedule;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'route_id' => 'required|exists:rs_routes,id',
            'vehicle_category_id' => 'nullable|exists:vehicle_categories,id',
            'days' => 'required|array|min:1',
            'days.*' => 'string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'departure_times' => 'required|array',
            'departure_times.*' => 'required|string|max:8', // 'HH:MM'

            'quota' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        RideSharingSchedule::create($validated);

        return redirect()->back()->with('success', 'Schedule added successfully.');
    }

    public function update(Request $request, RideSharingSchedule $rs_schedule)
    {
        $validated = $request->validate([
            'vehicle_category_id' => 'nullable|exists:vehicle_categories,id',
            'days' => 'required|array|min:1',
            'days.*' => 'string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'departure_times' => 'required|array',
            'departure_times.*' => 'required|string|max:8', // 'HH:MM'
            'quota' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $rs_schedule->update($validated);

        return redirect()->back()->with('success', 'Schedule updated successfully.');
    }

    public function destroy(RideSharingSchedule $rs_schedule)
    {
        $rs_schedule->delete();

        return redirect()->back()->with('success', 'Schedule deleted successfully.');
    }
}
