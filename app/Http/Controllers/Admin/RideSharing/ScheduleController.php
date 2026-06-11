<?php

namespace App\Http\Controllers\Admin\RideSharing;

use App\Http\Controllers\Controller;
use App\Models\RideSharingRoutePrice;
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
            'prices' => 'array',
            'prices.*.from_city_id' => 'required|exists:rs_cities,id',
            'prices.*.to_city_id' => 'required|exists:rs_cities,id',
            'prices.*.price' => 'nullable|numeric|min:0',
            'prices.*.is_active' => 'boolean',
        ]);

        $schedule = RideSharingSchedule::create($validated);
        $this->syncPrices($schedule, $validated['prices'] ?? []);

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
            'prices' => 'array',
            'prices.*.from_city_id' => 'required|exists:rs_cities,id',
            'prices.*.to_city_id' => 'required|exists:rs_cities,id',
            'prices.*.price' => 'nullable|numeric|min:0',
            'prices.*.is_active' => 'boolean',
        ]);

        $rs_schedule->update($validated);
        $this->syncPrices($rs_schedule, $validated['prices'] ?? []);

        return redirect()->back()->with('success', 'Schedule updated successfully.');
    }

    public function destroy(RideSharingSchedule $rs_schedule)
    {
        $rs_schedule->delete();

        return redirect()->back()->with('success', 'Schedule deleted successfully.');
    }

    private function syncPrices(RideSharingSchedule $schedule, array $pricesData)
    {
        foreach ($pricesData as $priceData) {
            $isActive = $priceData['is_active'] ?? true;

            // If the price is null but active, we could save it as null, or just skip.
            // In the previous logic we deleted if null. Since we now have is_active,
            // let's just save the record with its is_active state,
            // unless price is literally missing and we want to clear it.
            // But wait, the user asked for: "non aktif berarti gabisa berangkat... dan harganya otomatis disable"
            // So we just save the is_active status.

            RideSharingRoutePrice::updateOrCreate(
                [
                    'route_id' => $schedule->route_id,
                    'schedule_id' => $schedule->id,
                    'from_city_id' => $priceData['from_city_id'],
                    'to_city_id' => $priceData['to_city_id'],
                ],
                [
                    'price' => $priceData['price'],
                    'is_active' => $isActive,
                ]
            );
        }
    }
}
