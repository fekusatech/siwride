<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LocationSearchController extends Controller
{
    /**
     * Curated static list of common Bali locations with their coordinates.
     * Used when no Google Maps API key is configured.
     *
     * @var array<int, array{name: string, address: string, lat: float, lng: float}>
     */
    private array $baliLocations = [
        // Airports
        ['name' => 'Ngurah Rai International Airport', 'address' => 'Jl. Airport Ngurah Rai, Tuban, Badung, Bali', 'lat' => -8.7488, 'lng' => 115.1670],
        // Kuta
        ['name' => 'Kuta Beach', 'address' => 'Kuta, Badung, Bali', 'lat' => -8.7184, 'lng' => 115.1686],
        ['name' => 'Hard Rock Hotel Bali', 'address' => 'Kuta, Badung, Bali', 'lat' => -8.7186, 'lng' => 115.1706],
        ['name' => 'Mercure Bali Kuta', 'address' => 'Kuta, Badung, Bali', 'lat' => -8.7213, 'lng' => 115.1706],
        ['name' => 'The Anvaya Beach Resort', 'address' => 'Kuta, Badung, Bali', 'lat' => -8.7255, 'lng' => 115.1666],
        ['name' => 'Bali Dynasty Resort', 'address' => 'Kuta, Badung, Bali', 'lat' => -8.7332, 'lng' => 115.1683],
        ['name' => 'Sheraton Bali Kuta Resort', 'address' => 'Kuta, Badung, Bali', 'lat' => -8.7254, 'lng' => 115.1672],
        ['name' => 'Kuta Paradiso Hotel', 'address' => 'Kuta, Badung, Bali', 'lat' => -8.7184, 'lng' => 115.1700],
        ['name' => 'Hotel Savanna Bali', 'address' => 'Kuta, Badung, Bali', 'lat' => -8.7209, 'lng' => 115.1683],
        // Legian
        ['name' => 'Legian Beach', 'address' => 'Legian, Kuta, Badung, Bali', 'lat' => -8.7019, 'lng' => 115.1657],
        ['name' => 'Swiss-Belinn Legian', 'address' => 'Legian, Kuta, Badung, Bali', 'lat' => -8.7039, 'lng' => 115.1673],
        ['name' => 'Padma Resort Legian', 'address' => 'Legian, Kuta, Badung, Bali', 'lat' => -8.7090, 'lng' => 115.1607],
        // Seminyak
        ['name' => 'Seminyak Beach', 'address' => 'Seminyak, Kuta, Badung, Bali', 'lat' => -8.6894, 'lng' => 115.1565],
        ['name' => 'Double Six Beach', 'address' => 'Seminyak, Kuta, Badung, Bali', 'lat' => -8.6877, 'lng' => 115.1593],
        ['name' => 'Potato Head Beach Club', 'address' => 'Seminyak, Kuta, Badung, Bali', 'lat' => -8.6887, 'lng' => 115.1567],
        ['name' => 'Ku De Ta', 'address' => 'Seminyak, Kuta, Badung, Bali', 'lat' => -8.6842, 'lng' => 115.1576],
        ['name' => 'The Legian Seminyak', 'address' => 'Seminyak, Kuta, Badung, Bali', 'lat' => -8.6890, 'lng' => 115.1598],
        ['name' => 'W Bali Seminyak', 'address' => 'Seminyak, Kuta, Badung, Bali', 'lat' => -8.6939, 'lng' => 115.1633],
        ['name' => 'Alila Seminyak', 'address' => 'Seminyak, Kuta, Badung, Bali', 'lat' => -8.6987, 'lng' => 115.1640],
        ['name' => 'The Layar Seminyak', 'address' => 'Seminyak, Kuta, Badung, Bali', 'lat' => -8.6929, 'lng' => 115.1604],
        // Canggu
        ['name' => 'Canggu Beach', 'address' => 'Canggu, Kuta Utara, Badung, Bali', 'lat' => -8.6466, 'lng' => 115.1326],
        ['name' => 'Echo Beach Canggu', 'address' => 'Canggu, Kuta Utara, Badung, Bali', 'lat' => -8.6519, 'lng' => 115.1267],
        ['name' => 'Batu Bolong Beach', 'address' => 'Canggu, Kuta Utara, Badung, Bali', 'lat' => -8.6509, 'lng' => 115.1303],
        ['name' => 'COMO Uma Canggu', 'address' => 'Canggu, Kuta Utara, Badung, Bali', 'lat' => -8.6522, 'lng' => 115.1298],
        // Jimbaran
        ['name' => 'Jimbaran Beach', 'address' => 'Jimbaran, South Kuta, Badung, Bali', 'lat' => -8.7804, 'lng' => 115.1639],
        ['name' => 'Four Seasons Resort Jimbaran', 'address' => 'Jimbaran, Badung, Bali', 'lat' => -8.7916, 'lng' => 115.1618],
        // Nusa Dua
        ['name' => 'Nusa Dua Beach', 'address' => 'Nusa Dua, Badung, Bali', 'lat' => -8.8005, 'lng' => 115.2320],
        ['name' => 'Grand Hyatt Bali', 'address' => 'Nusa Dua, Badung, Bali', 'lat' => -8.8009, 'lng' => 115.2305],
        ['name' => 'Mulia Resort Nusa Dua', 'address' => 'Nusa Dua, Badung, Bali', 'lat' => -8.7993, 'lng' => 115.2371],
        ['name' => 'Sofitel Bali Nusa Dua', 'address' => 'Nusa Dua, Badung, Bali', 'lat' => -8.8024, 'lng' => 115.2297],
        ['name' => 'Bali Nusa Dua Convention Center', 'address' => 'Nusa Dua, Badung, Bali', 'lat' => -8.8022, 'lng' => 115.2319],
        // Uluwatu
        ['name' => 'Uluwatu Temple (Pura Luhur Uluwatu)', 'address' => 'Pecatu, South Kuta, Badung, Bali', 'lat' => -8.8292, 'lng' => 115.0849],
        ['name' => 'Uluwatu Beach', 'address' => 'Pecatu, South Kuta, Badung, Bali', 'lat' => -8.8292, 'lng' => 115.0849],
        ['name' => 'Padang Padang Beach', 'address' => 'Pecatu, South Kuta, Badung, Bali', 'lat' => -8.8120, 'lng' => 115.0956],
        ['name' => 'Suluban Beach (Blue Point)', 'address' => 'Pecatu, South Kuta, Badung, Bali', 'lat' => -8.8199, 'lng' => 115.0869],
        // Denpasar
        ['name' => 'Bali Museum (Museum Bali)', 'address' => 'Denpasar, Bali', 'lat' => -8.6569, 'lng' => 115.2164],
        ['name' => 'Bajra Sandhi Monument', 'address' => 'Renon, Denpasar, Bali', 'lat' => -8.6645, 'lng' => 115.2377],
        ['name' => 'Denpasar City Center', 'address' => 'Denpasar, Bali', 'lat' => -8.6602, 'lng' => 115.2204],
        // Sanur
        ['name' => 'Sanur Beach', 'address' => 'Sanur, Denpasar Selatan, Denpasar, Bali', 'lat' => -8.7083, 'lng' => 115.2620],
        ['name' => 'Mertasari Beach', 'address' => 'Sanur, Denpasar, Bali', 'lat' => -8.7186, 'lng' => 115.2659],
        // Ubud
        ['name' => 'Ubud Palace (Puri Saren Agung)', 'address' => 'Ubud, Gianyar, Bali', 'lat' => -8.5069, 'lng' => 115.2624],
        ['name' => 'Ubud Monkey Forest', 'address' => 'Ubud, Gianyar, Bali', 'lat' => -8.5185, 'lng' => 115.2583],
        ['name' => 'Campuhan Ridge Walk', 'address' => 'Ubud, Gianyar, Bali', 'lat' => -8.5053, 'lng' => 115.2524],
        ['name' => 'Goa Gajah (Elephant Cave)', 'address' => 'Bedulu, Blahbatuh, Gianyar, Bali', 'lat' => -8.5237, 'lng' => 115.2880],
        ['name' => 'Komaneka at Bisma Ubud', 'address' => 'Ubud, Gianyar, Bali', 'lat' => -8.5082, 'lng' => 115.2640],
        ['name' => 'COMO Shambhala Estate', 'address' => 'Ubud, Gianyar, Bali', 'lat' => -8.4970, 'lng' => 115.2517],
        ['name' => 'Maya Ubud Resort', 'address' => 'Ubud, Gianyar, Bali', 'lat' => -8.4986, 'lng' => 115.2797],
        ['name' => 'Alaya Resort Ubud', 'address' => 'Ubud, Gianyar, Bali', 'lat' => -8.5070, 'lng' => 115.2620],
        ['name' => 'Mandapa Ritz-Carlton Reserve', 'address' => 'Kedewatan, Ubud, Gianyar, Bali', 'lat' => -8.5052, 'lng' => 115.2432],
        // Tegallalang
        ['name' => 'Tegallalang Rice Terrace', 'address' => 'Tegallalang, Gianyar, Bali', 'lat' => -8.4311, 'lng' => 115.2778],
        // Tanah Lot
        ['name' => 'Tanah Lot Temple', 'address' => 'Beraban, Kediri, Tabanan, Bali', 'lat' => -8.6215, 'lng' => 115.0863],
        // Bedugul
        ['name' => 'Ulun Danu Beratan Temple', 'address' => 'Candi Kuning, Baturiti, Tabanan, Bali', 'lat' => -8.2759, 'lng' => 115.1661],
        ['name' => 'Bedugul Botanical Garden', 'address' => 'Candi Kuning, Baturiti, Tabanan, Bali', 'lat' => -8.2770, 'lng' => 115.1680],
        // Kintamani
        ['name' => 'Mount Batur (Gunung Batur)', 'address' => 'Kintamani, Bangli, Bali', 'lat' => -8.2422, 'lng' => 115.3748],
        ['name' => 'Lake Batur (Danau Batur)', 'address' => 'Kintamani, Bangli, Bali', 'lat' => -8.2565, 'lng' => 115.3857],
        // Karangasem
        ['name' => 'Lempuyang Temple (Gates of Heaven)', 'address' => 'Lempuyang, Karangasem, Bali', 'lat' => -8.3922, 'lng' => 115.6259],
        ['name' => 'Tirta Gangga Water Palace', 'address' => 'Ababi, Abang, Karangasem, Bali', 'lat' => -8.4115, 'lng' => 115.5761],
        ['name' => 'Amed Beach', 'address' => 'Amed, Abang, Karangasem, Bali', 'lat' => -8.3481, 'lng' => 115.6363],
        // Lovina (North Bali)
        ['name' => 'Lovina Beach', 'address' => 'Lovina, Buleleng, Bali', 'lat' => -8.1591, 'lng' => 115.0195],
    ];

    /**
     * Search for locations within active admin-configured zones.
     *
     * Priority:
     * 1. If active zones exist → restrict results to those zones
     * 2. Google Places API (if key configured) → bias to zone bounding box
     * 3. Static list → filter by zone polygons using PHP point-in-polygon
     * 4. If NO active zones → allow all Bali locations (open mode)
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => ['required', 'string', 'min:1', 'max:100'],
        ]);

        $query = trim($request->string('q'));
        $apiKey = config('services.google.maps_api_key');

        // Load active zone polygons from the database
        $activeZones = $this->loadActiveZonePolygons();

        if ($apiKey) {
            return $this->searchWithGooglePlaces($query, $apiKey, $activeZones);
        }

        return $this->searchFromStaticList($query, $activeZones);
    }

    /**
     * Load all active zones with their polygon coordinates parsed from WKT.
     *
     * @return array<int, array{id: int, name: string, polygon: array<array{lat: float, lng: float}>}>
     */
    private function loadActiveZonePolygons(): array
    {
        try {
            return Zone::where('is_active', true)
                ->select(['id', 'name', DB::raw('ST_AsText(coordinates) as coordinates_wkt')])
                ->get()
                ->map(function ($zone) {
                    $polygon = $this->parseWktPolygon($zone->coordinates_wkt ?? '');

                    return [
                        'id' => $zone->id,
                        'name' => $zone->name,
                        'polygon' => $polygon,
                    ];
                })
                ->filter(fn ($z) => count($z['polygon']) >= 3)
                ->values()
                ->all();
        } catch (\Exception $e) {
            Log::warning("LocationSearchController: Failed to load zones: {$e->getMessage()}");

            return [];
        }
    }

    /**
     * Parse a WKT POLYGON string into an array of {lat, lng} points.
     * The app stores coordinates as POLYGON((lat lng, lat lng, ...)).
     *
     * @return array<array{lat: float, lng: float}>
     */
    private function parseWktPolygon(string $wkt): array
    {
        if (! preg_match('/\(\((.*?)\)\)/', $wkt, $matches)) {
            return [];
        }

        return collect(explode(',', $matches[1]))
            ->map(function ($point) {
                $parts = explode(' ', trim($point));

                return ['lat' => (float) ($parts[0] ?? 0), 'lng' => (float) ($parts[1] ?? 0)];
            })
            ->all();
    }

    /**
     * Compute the bounding box [{minLat, maxLat, minLng, maxLng}] of all active zone polygons.
     *
     * @param  array<int, array{polygon: array<array{lat: float, lng: float}>}>  $zones
     * @return array{minLat: float, maxLat: float, minLng: float, maxLng: float, centerLat: float, centerLng: float, radiusMeters: int}|null
     */
    private function computeBoundingBox(array $zones): ?array
    {
        if (empty($zones)) {
            return null;
        }

        $allPoints = collect($zones)->flatMap(fn ($z) => $z['polygon']);

        $minLat = $allPoints->min('lat');
        $maxLat = $allPoints->max('lat');
        $minLng = $allPoints->min('lng');
        $maxLng = $allPoints->max('lng');

        $centerLat = ($minLat + $maxLat) / 2;
        $centerLng = ($minLng + $maxLng) / 2;

        // Rough radius: half the diagonal in meters (1 deg lat ≈ 111 km)
        $latKm = ($maxLat - $minLat) * 111;
        $lngKm = ($maxLng - $minLng) * 111 * cos(deg2rad($centerLat));
        $radiusMeters = (int) (sqrt($latKm ** 2 + $lngKm ** 2) / 2 * 1000);

        return compact('minLat', 'maxLat', 'minLng', 'maxLng', 'centerLat', 'centerLng', 'radiusMeters');
    }

    /**
     * Check whether a point (lat, lng) lies inside a polygon using ray casting.
     * The polygon is an array of {lat, lng} vertices.
     *
     * @param  array<array{lat: float, lng: float}>  $polygon
     */
    private function isPointInPolygon(float $lat, float $lng, array $polygon): bool
    {
        $n = count($polygon);
        if ($n < 3) {
            return false;
        }

        $inside = false;
        $j = $n - 1;

        for ($i = 0; $i < $n; $i++) {
            $xi = $polygon[$i]['lat'];
            $yi = $polygon[$i]['lng'];
            $xj = $polygon[$j]['lat'];
            $yj = $polygon[$j]['lng'];

            $intersect = (($yi > $lng) !== ($yj > $lng))
                && ($lat < ($xj - $xi) * ($lng - $yi) / ($yj - $yi) + $xi);

            if ($intersect) {
                $inside = ! $inside;
            }

            $j = $i;
        }

        return $inside;
    }

    /**
     * Check if a point is inside ANY of the given zone polygons.
     *
     * @param  array<int, array{polygon: array<array{lat: float, lng: float}>}>  $zones
     */
    private function isInsideAnyZone(float $lat, float $lng, array $zones): bool
    {
        foreach ($zones as $zone) {
            if ($this->isPointInPolygon($lat, $lng, $zone['polygon'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Proxy Google Places Autocomplete, biased/restricted to active zone bounding box.
     *
     * @param  array<int, array{polygon: array<array{lat: float, lng: float}>}>  $activeZones
     */
    private function searchWithGooglePlaces(string $query, string $apiKey, array $activeZones): JsonResponse
    {
        $bbox = $this->computeBoundingBox($activeZones);

        // Use the zone bounding box as the bias center; fall back to Bali center if no zones
        $centerLat = $bbox['centerLat'] ?? -8.4095;
        $centerLng = $bbox['centerLng'] ?? 115.1889;
        $radius = $bbox['radiusMeters'] ?? 100000;

        try {
            $response = Http::timeout(5)->get('https://maps.googleapis.com/maps/api/place/autocomplete/json', [
                'input' => $query,
                'key' => $apiKey,
                'components' => 'country:id',
                'location' => "{$centerLat},{$centerLng}",
                'radius' => min($radius + 20000, 150000), // add 20 km buffer
                'strictbounds' => ! empty($activeZones),   // restrict when zones are active
                'language' => 'id',
                'types' => 'establishment|geocode',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $suggestions = collect($data['predictions'] ?? [])
                    ->map(fn ($p) => [
                        'name' => $p['structured_formatting']['main_text'] ?? $p['description'],
                        'address' => $p['description'],
                        'place_id' => $p['place_id'] ?? null,
                    ])
                    ->values()
                    ->all();

                return response()->json(['suggestions' => $suggestions]);
            }
        } catch (\Exception $e) {
            Log::warning("LocationSearchController: Google Places API error: {$e->getMessage()}");
        }

        // Fall through to static list on Google API error
        return $this->searchFromStaticList($query, $activeZones);
    }

    /**
     * Filter the static location list by query text and active zone polygons.
     *
     * @param  array<int, array{polygon: array<array{lat: float, lng: float}>}>  $activeZones
     */
    private function searchFromStaticList(string $query, array $activeZones): JsonResponse
    {
        $lowerQuery = mb_strtolower($query);
        $hasZones = ! empty($activeZones);

        $suggestions = collect($this->baliLocations)
            ->filter(function ($location) use ($lowerQuery, $hasZones, $activeZones) {
                // Text match first
                $textMatch = str_contains(mb_strtolower($location['name']), $lowerQuery)
                    || str_contains(mb_strtolower($location['address']), $lowerQuery);

                if (! $textMatch) {
                    return false;
                }

                // If zones are configured, check if this location is inside any active zone
                if ($hasZones) {
                    return $this->isInsideAnyZone($location['lat'], $location['lng'], $activeZones);
                }

                // No zones configured → allow all Bali locations
                return true;
            })
            ->take(8)
            ->map(fn ($l) => [
                'name' => $l['name'],
                'address' => $l['address'],
                'place_id' => null,
            ])
            ->values()
            ->all();

        return response()->json(['suggestions' => $suggestions]);
    }
}
