<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\DriverService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CatalogController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->input('search', '');
        $type = $request->input('type', '');
        $sort = $request->input('sort', 'newest');
        $minPrice = (float) ($request->input('min_price') ?? 0);
        $maxPrice = (float) ($request->input('max_price') ?? 0);

        $services = collect();
        $activities = collect();

        if ($type === '' || $type === 'service') {
            $services = DriverService::query()
                ->with('driver')
                ->where('status', DriverService::STATUS_APPROVED)
                ->whereNotNull('price_per_pax')
                ->when($search, function ($query) use ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('title', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%")
                            ->orWhere('meeting_point', 'like', "%{$search}%");
                    });
                })
                ->when($minPrice > 0, fn ($q) => $q->where('price_per_pax', '>=', $minPrice))
                ->when($maxPrice > 0, fn ($q) => $q->where('price_per_pax', '<=', $maxPrice))
                ->get()
                ->map(fn (DriverService $s) => [
                    'id' => $s->id,
                    'type' => 'service',
                    'slug' => $s->slug,
                    'title' => $s->title,
                    'subtitle' => $s->duration_label,
                    'description' => $s->description,
                    'image_url' => $s->image_url,
                    'price_per_pax' => (float) $s->price_per_pax,
                    'duration_label' => $s->duration_label,
                    'min_pax' => $s->min_pax,
                    'max_pax' => $s->max_pax,
                    'driver_name' => $s->driver?->name ?? 'Siwride Driver',
                    'driver_id' => $s->driver?->id,
                    'detail_url' => "/services/{$s->slug}",
                ]);
        }

        if ($type === '' || $type === 'activity') {
            $activities = Activity::query()
                ->where('is_active', true)
                ->whereNotNull('price_per_pax')
                ->when($search, function ($query) use ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('title', 'like', "%{$search}%")
                            ->orWhere('subtitle', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%")
                            ->orWhere('meeting_point', 'like', "%{$search}%");
                    });
                })
                ->when($minPrice > 0, fn ($q) => $q->where('price_per_pax', '>=', $minPrice))
                ->when($maxPrice > 0, fn ($q) => $q->where('price_per_pax', '<=', $maxPrice))
                ->get()
                ->map(fn (Activity $a) => [
                    'id' => $a->id,
                    'type' => 'activity',
                    'slug' => $a->slug,
                    'title' => $a->title,
                    'subtitle' => $a->subtitle,
                    'description' => $a->description,
                    'image_url' => $a->image_url,
                    'price_per_pax' => (float) $a->price_per_pax,
                    'duration_label' => $a->duration_label,
                    'min_pax' => $a->min_pax,
                    'max_pax' => $a->max_pax,
                    'driver_name' => null,
                    'detail_url' => "/activities/{$a->slug}",
                ]);
        }

        $items = $services->concat($activities);

        $items = match ($sort) {
            'price_low' => $items->sortBy('price_per_pax')->values(),
            'price_high' => $items->sortByDesc('price_per_pax')->values(),
            'title' => $items->sortBy('title')->values(),
            default => $items->sortByDesc('id')->values(),
        };

        return Inertia::render('customer/catalog', [
            'items' => $items,
            'filters' => [
                'search' => $search,
                'type' => $type,
                'sort' => $sort,
                'min_price' => $minPrice > 0 ? $minPrice : null,
                'max_price' => $maxPrice > 0 ? $maxPrice : null,
            ],
        ]);
    }
}
