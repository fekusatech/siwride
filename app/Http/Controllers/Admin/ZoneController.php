<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ZoneController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        // Select ST_AsText to get the WKT representation of the polygon
        $zones = Zone::select(['id', 'name', 'is_active', DB::raw('ST_AsText(coordinates) as coordinates_wkt')])
            ->get()
            ->map(function ($zone) {
                $wkt = $zone->coordinates_wkt;
                if (! $wkt) {
                    $coords = [];
                } else {
                    $wkt = str_replace(['POLYGON((', '))'], '', $wkt);
                    $points = explode(',', $wkt);
                    $coords = array_map(function ($point) {
                        $p = explode(' ', trim($point));

                        return ['lat' => (float) ($p[0] ?? 0), 'lng' => (float) ($p[1] ?? 0)];
                    }, $points);

                    // Google Maps Polygon doesn't need the closing point duplicated
                    if (count($coords) > 1) {
                        $first = $coords[0];
                        $last = $coords[count($coords) - 1];
                        if ($first['lat'] === $last['lat'] && $first['lng'] === $last['lng']) {
                            array_pop($coords);
                        }
                    }
                }

                return [
                    'id' => $zone->id,
                    'name' => $zone->name,
                    'is_active' => $zone->is_active,
                    'coordinates' => $coords,
                ];
            });

        return Inertia::render('Admin/Zones/Index', [
            'zones' => $zones,
            'google_maps_api_key' => config('services.google.maps_api_key'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'coordinates' => 'required|array|min:3',
            'coordinates.*.lat' => 'required|numeric',
            'coordinates.*.lng' => 'required|numeric',
            'is_active' => 'boolean',
        ]);

        Zone::create($validated);

        return redirect()->back()->with('success', 'Zone created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Zone $zone)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'coordinates' => 'required|array|min:3',
            'coordinates.*.lat' => 'required|numeric',
            'coordinates.*.lng' => 'required|numeric',
            'is_active' => 'boolean',
        ]);

        $zone->update($validated);

        return redirect()->back()->with('success', 'Zone updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Zone $zone)
    {
        $zone->delete();

        return redirect()->back()->with('success', 'Zone deleted successfully.');
    }

    /**
     * Validate if a point is within any active zone.
     */
    public function validatePoint(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        $inside = Zone::containsPoint($request->lat, $request->lng);

        return response()->json(['inside' => $inside]);
    }
}
