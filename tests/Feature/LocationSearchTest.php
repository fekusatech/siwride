<?php

use App\Models\Zone;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::preventStrayRequests();
});

// Helper: create a simple square zone polygon using raw SQL (bypasses the model's set accessor)
function createTestZone(string $name, float $minLat, float $maxLat, float $minLng, float $maxLng): Zone
{
    $wkt = "POLYGON(($minLat $minLng,$maxLat $minLng,$maxLat $maxLng,$minLat $maxLng,$minLat $minLng))";
    $zone = new Zone(['name' => $name, 'is_active' => true]);
    DB::table('zones')->insert([
        'name' => $name,
        'is_active' => true,
        'coordinates' => DB::raw("ST_GeomFromText('$wkt')"),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return Zone::where('name', $name)->latest()->first();
}

it('returns suggestions filtered by active zones from database', function () {
    Http::fake(['nominatim.openstreetmap.org/*' => Http::response([
        ['display_name' => 'Ubud Palace, Bali', 'lat' => '-8.5069', 'lon' => '115.2624'],
    ])]);
    // Create a zone that covers Kuta area (but NOT Ubud)
    createTestZone('Test Kuta Zone', -8.74, -8.70, 115.15, 115.19);

    // Kuta Beach is at -8.7184, 115.1686 → inside zone
    $response = $this->getJson('/locations/search?q=kuta');
    $response->assertOk();
    $names = collect($response->json('suggestions'))->pluck('name');
    expect($names->filter(fn ($n) => str_contains(strtolower($n), 'kuta'))->count())->toBeGreaterThan(0);

    // Ubud Palace is at -8.5069, 115.2624 → outside zone → should NOT appear for "ubud"
    $response2 = $this->getJson('/locations/search?q=ubud');
    $response2->assertOk();
    expect($response2->json('suggestions'))->toBeEmpty();
});

it('returns all bali locations when no active zones exist', function () {
    Http::fake(['nominatim.openstreetmap.org/*' => Http::response([])]);
    // No zones in DB (fresh RefreshDatabase state)
    $response = $this->getJson('/locations/search?q=ubud');

    $response->assertOk();
    $suggestions = $response->json('suggestions');
    expect($suggestions)->not->toBeEmpty();
});

it('returns ok with suggestions for single char query', function () {
    $response = $this->getJson('/locations/search?q=k');

    $response->assertOk()->assertJsonStructure(['suggestions']);
});

it('returns 422 when query param is missing', function () {
    $response = $this->getJson('/locations/search');

    $response->assertUnprocessable();
});

it('filters static list by zone when zone exists and limits to 8 results', function () {
    // Zone covering all of Bali (wide box)
    createTestZone('All Bali', -9.0, -8.0, 114.5, 116.0);

    $response = $this->getJson('/locations/search?q=bali');

    $response->assertOk();
    $suggestions = $response->json('suggestions');
    expect(count($suggestions))->toBeLessThanOrEqual(8);
});

it('enriches sparse static results using OpenStreetMap', function () {
    Http::fake([
        'nominatim.openstreetmap.org/*' => Http::response([
            ['display_name' => 'Pantai Test, Bali, Indonesia', 'lat' => '-8.7184', 'lon' => '115.1686'],
        ]),
    ]);

    $response = $this->getJson('/locations/search?q=pantai-test');

    $response->assertOk()
        ->assertJsonPath('suggestions.0.name', 'Pantai Test')
        ->assertJsonPath('suggestions.0.place_id', null)
        ->assertJsonPath('suggestions.0.lat', -8.7184)
        ->assertJsonPath('suggestions.0.lng', 115.1686);
    Http::assertSentCount(1);
});

it('falls back to static list when OpenStreetMap fails', function () {
    Http::fake([
        'nominatim.openstreetmap.org/*' => Http::response(null, 500),
    ]);

    $response = $this->getJson('/locations/search?q=campuhan');

    $response->assertOk()->assertJsonStructure(['suggestions']);
    expect($response->json('suggestions'))->not->toBeEmpty();
    Http::assertSentCount(1);
});
