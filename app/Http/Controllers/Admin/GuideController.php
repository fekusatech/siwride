<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VehicleCategory;
use App\Models\Zone;
use App\Models\ZonePricingRule;
use Inertia\Inertia;
use Inertia\Response;

class GuideController extends Controller
{
    /**
     * How zone-based pricing works, and the admin's actual current zones/
     * rules - read live from the database rather than a static writeup, so
     * it never drifts from what's really configured.
     */
    public function zonePricing(): Response
    {
        return Inertia::render('Admin/Guide/ZonePricing', [
            'zones' => Zone::orderBy('name')->get(['id', 'name', 'is_active']),
            'rules' => ZonePricingRule::with(['pickupZone:id,name', 'dropoffZone:id,name'])
                ->orderBy('pickup_zone_id')
                ->get()
                ->map(fn (ZonePricingRule $rule) => [
                    'pickup_zone' => $rule->pickupZone?->name,
                    'dropoff_zone' => $rule->dropoffZone?->name,
                    'base_price' => (float) $rule->base_price,
                    'minimum_price' => (float) $rule->minimum_price,
                    'distance_km' => (float) $rule->distance_km,
                    'is_active' => $rule->is_active,
                ]),
            'vehicle_categories' => VehicleCategory::orderBy('price_per_km')
                ->get(['title', 'price_per_km']),
        ]);
    }
}
