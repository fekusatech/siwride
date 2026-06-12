<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VehicleCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class VehicleCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('Admin/VehicleCategories/Index', [
            'categories' => VehicleCategory::query()
                ->when($request->search, function ($query, $search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('vehicle_type', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                })
                ->latest()
                ->paginate(10)
                ->withQueryString(),
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/VehicleCategories/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
            'capacity' => ['nullable', 'string', 'max:100'],
            'passenger_capacity' => ['nullable', 'integer', 'min:1', 'max:100'],
            'luggage_capacity' => ['nullable', 'integer', 'min:0', 'max:100'],
            'advantages' => ['nullable', 'array', 'max:3'],
            'advantages.*' => ['string', 'max:100'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'price_per_km' => ['required', 'numeric', 'min:0'],
            'examples' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'vehicle_type' => ['required', 'string', 'max:50'],
        ]);

        $validated['slug'] = Str::slug($validated['title']);

        // Ensure slug is unique
        $originalSlug = $validated['slug'];
        $count = 1;
        while (VehicleCategory::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $originalSlug.'-'.$count;
            $count++;
        }

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('vehicles', 'public');
        }

        VehicleCategory::create($validated);

        return redirect()->route('admin.vehicle-categories.index')
            ->with('success', 'Vehicle Category created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(VehicleCategory $vehicleCategory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(VehicleCategory $vehicleCategory): Response
    {
        return Inertia::render('Admin/VehicleCategories/Create', [
            'category' => $vehicleCategory,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, VehicleCategory $vehicleCategory)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
            'capacity' => ['nullable', 'string', 'max:100'],
            'passenger_capacity' => ['nullable', 'integer', 'min:1', 'max:100'],
            'luggage_capacity' => ['nullable', 'integer', 'min:0', 'max:100'],
            'advantages' => ['nullable', 'array', 'max:3'],
            'advantages.*' => ['string', 'max:100'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'price_per_km' => ['required', 'numeric', 'min:0'],
            'examples' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'vehicle_type' => ['required', 'string', 'max:50'],
        ]);

        $validated['slug'] = Str::slug($validated['title']);

        // Ensure slug is unique, excluding current category
        $originalSlug = $validated['slug'];
        $count = 1;
        while (VehicleCategory::where('slug', $validated['slug'])->where('id', '!=', $vehicleCategory->id)->exists()) {
            $validated['slug'] = $originalSlug.'-'.$count;
            $count++;
        }

        if ($request->hasFile('image')) {
            // Delete old image if it exists and is stored in public disk
            if ($vehicleCategory->image && ! str_starts_with($vehicleCategory->image, '/assets/') && ! str_starts_with($vehicleCategory->image, 'assets/')) {
                Storage::disk('public')->delete($vehicleCategory->image);
            }
            $validated['image'] = $request->file('image')->store('vehicles', 'public');
        } else {
            unset($validated['image']);
        }

        $vehicleCategory->update($validated);

        return redirect()->back()
            ->with('success', 'Vehicle Category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VehicleCategory $vehicleCategory)
    {
        // Delete image file if it exists and is stored in public disk
        if ($vehicleCategory->image && ! str_starts_with($vehicleCategory->image, '/assets/') && ! str_starts_with($vehicleCategory->image, 'assets/')) {
            Storage::disk('public')->delete($vehicleCategory->image);
        }

        $vehicleCategory->delete();

        return redirect()->route('admin.vehicle-categories.index')
            ->with('success', 'Vehicle Category deleted successfully.');
    }
}
