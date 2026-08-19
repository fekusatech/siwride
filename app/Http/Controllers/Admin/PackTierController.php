<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PackTier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PackTierController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/PackTiers/Index', [
            'tiers' => PackTier::query()
                ->orderBy('min_pax')
                ->paginate(20)
                ->withQueryString()
                ->through(fn (PackTier $tier) => [
                    'id' => $tier->id,
                    'label' => $tier->label,
                    'min_pax' => $tier->min_pax,
                    'max_pax' => $tier->max_pax,
                    'discount_type' => $tier->discount_type,
                    'discount_value' => (float) $tier->discount_value,
                    'discount_label' => $tier->discountLabel(),
                    'sort_order' => $tier->sort_order,
                    'is_active' => $tier->is_active,
                ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:100'],
            'min_pax' => ['required', 'integer', 'min:1'],
            'max_pax' => ['nullable', 'integer', 'gte:min_pax'],
            'discount_type' => ['required', 'in:percent,flat'],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        PackTier::create($validated);

        return back()->with('success', 'Pack tier created.');
    }

    public function update(Request $request, PackTier $packTier): RedirectResponse
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:100'],
            'min_pax' => ['required', 'integer', 'min:1'],
            'max_pax' => ['nullable', 'integer', 'gte:min_pax'],
            'discount_type' => ['required', 'in:percent,flat'],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $packTier->update($validated);

        return back()->with('success', 'Pack tier updated.');
    }

    public function destroy(PackTier $packTier): RedirectResponse
    {
        $packTier->delete();

        return back()->with('success', 'Pack tier deleted.');
    }
}
