<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class DriverController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('Admin/Drivers/Index', [
            'drivers' => Driver::query()
                ->withCount('vehicles')
                ->when($request->search, function ($query, $search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('nid', 'like', "%{$search}%");
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
        return Inertia::render('Admin/Drivers/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:drivers'],
            'phone' => ['required', 'string', 'max:50', 'unique:drivers'],
            'password' => ['required', 'string', 'min:8'],
            'status' => ['required', 'string'],
            'nik' => ['nullable', 'string', 'max:20'],
            'nik_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'sim' => ['nullable', 'string', 'max:20'],
            'sim_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            
            // Optional Vehicle fields
            'add_vehicle' => ['boolean'],
            'v_brand' => ['required_if:add_vehicle,true', 'nullable', 'string', 'max:255'],
            'v_model' => ['required_if:add_vehicle,true', 'nullable', 'string', 'max:255'],
            'v_type' => ['nullable', 'string', 'max:100'],
            'v_registration_number' => ['required_if:add_vehicle,true', 'nullable', 'string', 'max:100', 'unique:vehicles,registration_number'],
            'v_color' => ['nullable', 'string', 'max:50'],
        ]);

        return DB::transaction(function () use ($request, $validated) {
            if ($request->hasFile('nik_image')) {
                $validated['nik_image'] = $request->file('nik_image')->store('drivers/nik', 'public');
            }

            if ($request->hasFile('sim_image')) {
                $validated['sim_image'] = $request->file('sim_image')->store('drivers/sim', 'public');
            }

            $driver = Driver::create($validated);

            if ($request->boolean('add_vehicle')) {
                $driver->vehicles()->create([
                    'brand' => $validated['v_brand'],
                    'model' => $validated['v_model'],
                    'type' => $validated['v_type'] ?? '',
                    'registration_number' => $validated['v_registration_number'],
                    'color' => $validated['v_color'] ?? '',
                    'status' => 'active',
                ]);
            }

            return redirect()->route('admin.drivers.index')
                ->with('success', 'Driver' . ($request->boolean('add_vehicle') ? ' and vehicle' : '') . ' created successfully.');
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(Driver $driver)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Driver $driver): Response
    {
        return Inertia::render('Admin/Drivers/Create', [
            'driver' => $driver,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Driver $driver)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:drivers,email,'.$driver->id],
            'phone' => ['required', 'string', 'max:50', 'unique:drivers,phone,'.$driver->id],
            'password' => ['nullable', 'string', 'min:8'],
            'status' => ['required', 'string'],
            'nik' => ['nullable', 'string', 'max:20'],
            'nik_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'sim' => ['nullable', 'string', 'max:20'],
            'sim_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        if ($request->hasFile('nik_image')) {
            if ($driver->nik_image) {
                Storage::disk('public')->delete($driver->nik_image);
            }
            $validated['nik_image'] = $request->file('nik_image')->store('drivers/nik', 'public');
        }

        if ($request->hasFile('sim_image')) {
            if ($driver->sim_image) {
                Storage::disk('public')->delete($driver->sim_image);
            }
            $validated['sim_image'] = $request->file('sim_image')->store('drivers/sim', 'public');
        }

        $driver->update($validated);

        return redirect()->route('admin.drivers.index')
            ->with('success', 'Driver updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Driver $driver)
    {
        $driver->delete();

        return redirect()->route('admin.drivers.index')
            ->with('success', 'Driver deleted successfully.');
    }
}
