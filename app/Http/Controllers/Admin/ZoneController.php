<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
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
                    preg_match('/\(\((.*?)\)\)/', $wkt, $matches);
                    $wktPoints = $matches[1] ?? '';
                    $points = explode(',', $wktPoints);
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

    public function boundarySuggestions(Request $request)
    {
        $validated = $request->validate([
            'query' => ['required', 'string', 'min:3', 'max:120'],
        ]);

        $response = Http::withHeaders([
            'User-Agent' => config('app.name', 'Siwride').'/1.0',
        ])
            ->timeout(10)
            ->connectTimeout(5)
            ->get('https://nominatim.openstreetmap.org/search', [
                'q' => $validated['query'],
                'format' => 'jsonv2',
                'polygon_geojson' => 1,
                'addressdetails' => 1,
                'limit' => 8,
                'countrycodes' => 'id',
            ]);

        $suggestions = collect($response->json())
            ->map(function (array $item) {
                $coordinates = $this->geoJsonToCoordinates($item['geojson'] ?? null);

                if (count($coordinates) < 3) {
                    return null;
                }

                return [
                    'name' => $item['display_name'] ?? 'Unknown Area',
                    'type' => $item['type'] ?? null,
                    'class' => $item['class'] ?? null,
                    'coordinates' => $coordinates,
                ];
            })
            ->filter()
            ->values();

        return response()->json([
            'data' => $suggestions,
        ]);
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

    /**
     * @return array<int, array{lat: float, lng: float}>
     */
    private function geoJsonToCoordinates(?array $geoJson): array
    {
        if (! $geoJson || ! isset($geoJson['type'], $geoJson['coordinates'])) {
            return [];
        }

        $points = match ($geoJson['type']) {
            'Polygon' => $geoJson['coordinates'][0] ?? [],
            'MultiPolygon' => collect($geoJson['coordinates'])
                ->sortByDesc(fn ($polygon) => count($polygon[0] ?? []))
                ->first()[0] ?? [],
            default => [],
        };

        return collect($points)
            ->map(fn ($point) => [
                'lat' => (float) ($point[1] ?? 0),
                'lng' => (float) ($point[0] ?? 0),
            ])
            ->filter(fn ($point) => $point['lat'] !== 0.0 && $point['lng'] !== 0.0)
            ->values()
            ->all();
    }
}
