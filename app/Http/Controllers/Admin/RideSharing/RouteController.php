<?php

namespace App\Http\Controllers\Admin\RideSharing;

use App\Http\Controllers\Controller;
use App\Models\RideSharingCity;
use App\Models\RideSharingRoute;
use App\Models\VehicleCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RouteController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $routes = RideSharingRoute::query()
            ->withCount('paths')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/RideSharing/Routes/Index', [
            'routes' => $routes,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function create()
    {
        $cities = RideSharingCity::orderBy('name')->get();

        return Inertia::render('Admin/RideSharing/Routes/Create', [
            'cities' => $cities,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'start_city_id' => 'required|exists:rs_cities,id',
            'end_city_id' => 'required|exists:rs_cities,id|different:start_city_id',
            'is_active' => 'boolean',
        ]);

        $startCity = RideSharingCity::find($validated['start_city_id']);
        $endCity = RideSharingCity::find($validated['end_city_id']);

        $route = RideSharingRoute::create([
            'name' => "{$startCity->name} - {$endCity->name}",
            'is_active' => $request->boolean('is_active', true),
        ]);

        $route->paths()->create([
            'city_id' => $startCity->id,
            'sequence' => 1,
        ]);
        $route->paths()->create([
            'city_id' => $endCity->id,
            'sequence' => 2,
        ]);

        return redirect()->route('admin.rs-routes.edit', $route->id)
            ->with('success', 'Route created successfully. Now configure its paths, prices, and schedules.');
    }

    public function edit(RideSharingRoute $rs_route)
    {
        // Load relationships for the detail/tabbed view
        $rs_route->load([
            'paths.city',
            'schedules.prices',
            'schedules.vehicleCategory',
        ]);

        $cities = RideSharingCity::orderBy('name')->get();
        $vehicleCategories = VehicleCategory::orderBy('title')->get();

        return Inertia::render('Admin/RideSharing/Routes/Edit', [
            'routeData' => $rs_route,
            'cities' => $cities,
            'vehicleCategories' => $vehicleCategories,
        ]);
    }

    public function update(Request $request, RideSharingRoute $rs_route)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'is_active' => 'boolean',
        ]);

        $rs_route->update($validated);

        return redirect()->back()->with('success', 'Route settings updated successfully.');
    }

    public function destroy(RideSharingRoute $rs_route)
    {
        $rs_route->delete();

        return redirect()->route('admin.rs-routes.index')
            ->with('success', 'Route deleted successfully.');
    }
}
