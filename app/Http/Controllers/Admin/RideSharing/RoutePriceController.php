<?php

namespace App\Http\Controllers\Admin\RideSharing;

use App\Http\Controllers\Controller;
use App\Models\RideSharingRoute;
use App\Models\RideSharingRoutePrice;
use Illuminate\Http\Request;

class RoutePriceController extends Controller
{
    /**
     * Update all prices for a specific route.
     * The frontend sends an array of price definitions.
     */
    public function updatePrices(Request $request, RideSharingRoute $rs_route)
    {
        $validated = $request->validate([
            'prices' => 'array',
            'prices.*.from_city_id' => 'required|exists:rs_cities,id',
            'prices.*.to_city_id' => 'required|exists:rs_cities,id',
            'prices.*.price' => 'nullable|numeric|min:0',
            'prices.*.estimated_minutes' => 'nullable|integer|min:0',
        ]);

        \DB::beginTransaction();

        try {
            foreach ($validated['prices'] ?? [] as $priceData) {
                // If price is null or empty, we might want to delete the entry or just set it
                // We'll only save positive numbers. If empty or 0, maybe remove it?
                // Let's say if it's set to null/empty, we delete the record so it's "unavailable".
                if (is_null($priceData['price']) || $priceData['price'] === '') {
                    RideSharingRoutePrice::where('route_id', $rs_route->id)
                        ->where('from_city_id', $priceData['from_city_id'])
                        ->where('to_city_id', $priceData['to_city_id'])
                        ->delete();
                } else {
                    RideSharingRoutePrice::updateOrCreate(
                        [
                            'route_id' => $rs_route->id,
                            'from_city_id' => $priceData['from_city_id'],
                            'to_city_id' => $priceData['to_city_id'],
                        ],
                        [
                            'price' => $priceData['price'],
                            'estimated_minutes' => $priceData['estimated_minutes'] ?? null,
                        ]
                    );
                }
            }

            \DB::commit();

            return redirect()->back()->with('success', 'Route prices updated successfully.');
        } catch (\Exception $e) {
            \DB::rollBack();

            return redirect()->back()->with('error', 'Failed to update route prices: '.$e->getMessage());
        }
    }
}
