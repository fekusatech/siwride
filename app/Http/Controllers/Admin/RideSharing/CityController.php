<?php

namespace App\Http\Controllers\Admin\RideSharing;

use App\Http\Controllers\Controller;
use App\Models\RideSharingCity;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CityController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $cities = RideSharingCity::query()
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('name', 'asc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/RideSharing/Cities/Index', [
            'cities' => $cities,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/RideSharing/Cities/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'address' => 'nullable|string|max:1000',
        ]);

        RideSharingCity::create($validated);

        return redirect()->route('admin.rs-cities.index')
            ->with('success', 'City created successfully.');
    }

    public function edit(RideSharingCity $rs_city)
    {
        return Inertia::render('Admin/RideSharing/Cities/Create', [
            'city' => $rs_city,
        ]);
    }

    public function update(Request $request, RideSharingCity $rs_city)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'address' => 'nullable|string|max:1000',
        ]);

        $rs_city->update($validated);

        return redirect()->route('admin.rs-cities.index')
            ->with('success', 'City updated successfully.');
    }

    public function destroy(RideSharingCity $rs_city)
    {
        $rs_city->delete();

        return redirect()->route('admin.rs-cities.index')
            ->with('success', 'City deleted successfully.');
    }
}
