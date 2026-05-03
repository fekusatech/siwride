<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Zone;
use App\Models\ZonePricingRule;
use App\Services\ZonePricingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ZonePricingRuleController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Zones/Pricing', [
            'zones' => Zone::query()->orderBy('name')->get(['id', 'name', 'is_active']),
            'pricing_rules' => ZonePricingRule::query()
                ->with(['pickupZone:id,name', 'dropoffZone:id,name'])
                ->latest()
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatedData($request);

        ZonePricingRule::create($validated);

        return redirect()->back()->with('success', 'Zone pricing rule created successfully.');
    }

    public function update(Request $request, ZonePricingRule $zonePricingRule)
    {
        $validated = $this->validatedData($request, $zonePricingRule);

        $zonePricingRule->update($validated);

        return redirect()->back()->with('success', 'Zone pricing rule updated successfully.');
    }

    public function destroy(ZonePricingRule $zonePricingRule)
    {
        $zonePricingRule->delete();

        return redirect()->back()->with('success', 'Zone pricing rule deleted successfully.');
    }

    public function calculate(Request $request, ZonePricingService $pricingService): JsonResponse
    {
        $validated = $request->validate([
            'pickup_latitude' => ['required', 'numeric'],
            'pickup_longitude' => ['required', 'numeric'],
            'dropoff_latitude' => ['required', 'numeric'],
            'dropoff_longitude' => ['required', 'numeric'],
            'distance_km' => ['nullable', 'numeric', 'min:0'],
        ]);

        return response()->json($pricingService->calculate(
            (float) $validated['pickup_latitude'],
            (float) $validated['pickup_longitude'],
            (float) $validated['dropoff_latitude'],
            (float) $validated['dropoff_longitude'],
            isset($validated['distance_km']) ? (float) $validated['distance_km'] : null,
        ));
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?ZonePricingRule $zonePricingRule = null): array
    {
        return $request->validate([
            'pickup_zone_id' => [
                'required',
                'exists:zones,id',
                Rule::unique('zone_pricing_rules')
                    ->where('dropoff_zone_id', $request->input('dropoff_zone_id'))
                    ->ignore($zonePricingRule?->id),
            ],
            'dropoff_zone_id' => ['required', 'exists:zones,id'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'price_per_km' => ['required', 'numeric', 'min:0'],
            'minimum_price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);
    }
}
