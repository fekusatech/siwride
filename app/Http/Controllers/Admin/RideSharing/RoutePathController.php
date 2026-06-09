<?php

namespace App\Http\Controllers\Admin\RideSharing;

use App\Http\Controllers\Controller;
use App\Models\RideSharingRoute;
use App\Models\RideSharingRoutePath;
use App\Models\RideSharingRoutePrice;
use Illuminate\Http\Request;

class RoutePathController extends Controller
{
    /**
     * Update all paths for a specific route.
     * The frontend sends the complete ordered array of cities.
     */
    public function updatePaths(Request $request, RideSharingRoute $rs_route)
    {
        $validated = $request->validate([
            'paths' => 'array',
            'paths.*.city_id' => 'required|exists:rs_cities,id',
        ]);

        // Begin Transaction
        \DB::beginTransaction();

        try {
            $newCityIds = collect($validated['paths'] ?? [])->pluck('city_id')->toArray();

            // Delete old paths
            $rs_route->paths()->delete();

            // Insert new paths with sequence
            $insertData = [];
            foreach ($validated['paths'] ?? [] as $index => $pathData) {
                $insertData[] = [
                    'route_id' => $rs_route->id,
                    'city_id' => $pathData['city_id'],
                    'sequence' => $index + 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            if (! empty($insertData)) {
                RideSharingRoutePath::insert($insertData);
            }

            // Cleanup invalid prices (if a city was removed from the route)
            // Any price that has from_city_id or to_city_id not in $newCityIds should be deleted.
            RideSharingRoutePrice::where('route_id', $rs_route->id)
                ->where(function ($query) use ($newCityIds) {
                    $query->whereNotIn('from_city_id', $newCityIds)
                        ->orWhereNotIn('to_city_id', $newCityIds);
                })->delete();

            \DB::commit();

            return redirect()->back()->with('success', 'Route paths updated successfully.');
        } catch (\Exception $e) {
            \DB::rollBack();

            return redirect()->back()->with('error', 'Failed to update route paths: '.$e->getMessage());
        }
    }
}
