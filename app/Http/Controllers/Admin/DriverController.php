<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('nid', 'like', "%{$search}%");
                    });
                })
                ->latest()
                ->paginate(10)
                ->withQueryString()
                ->through(function ($driver) {
                    // Check if user account exists
                    $driver->has_user_account = User::where('email', $driver->email)->exists();

                    return $driver;
                }),
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Create a user account for an existing driver.
     */
    public function syncUser(Driver $driver)
    {
        if (User::where('email', $driver->email)->exists()) {
            return redirect()->back()->with('error', 'User account already exists for this driver.');
        }

        // Split name for User model
        $nameParts = explode(' ', $driver->name, 2);
        $firstname = $nameParts[0];
        $lastname = $nameParts[1] ?? '';

        User::create([
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email' => $driver->email,
            'phone' => $driver->phone,
            'password' => Hash::make($driver->email), // Default password is email
            'role' => 'driver',
            'status' => $driver->status,
            'nid' => $driver->nid,
        ]);

        return redirect()->back()->with('success', 'User account created successfully. Default password is the driver\'s email.');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Drivers/Create', [
            'default_nid' => $this->generateUniqueNid(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nid' => ['nullable', 'string', 'max:20', 'unique:drivers'],
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
            if (empty($validated['nid'])) {
                $validated['nid'] = $this->generateUniqueNid();
            }

            if ($request->hasFile('nik_image')) {
                $validated['nik_image'] = $request->file('nik_image')->store('drivers/nik', 'public');
            }

            if ($request->hasFile('sim_image')) {
                $validated['sim_image'] = $request->file('sim_image')->store('drivers/sim', 'public');
            }

            $driver = Driver::create($validated);

            // Split name for User model
            $nameParts = explode(' ', $validated['name'], 2);
            $firstname = $nameParts[0];
            $lastname = $nameParts[1] ?? '';

            // Create User record for Authentication
            User::create([
                'firstname' => $firstname,
                'lastname' => $lastname,
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
                'role' => 'driver',
                'status' => $validated['status'],
                'nid' => $validated['nid'],
            ]);

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
                ->with('success', 'Driver'.($request->boolean('add_vehicle') ? ' and vehicle' : '').' created successfully.');
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
            'default_nid' => $this->generateUniqueNid(),
        ]);
    }

    private function generateUniqueNid(): string
    {
        do {
            $nid = 'DRV-'.strtoupper(substr(md5(uniqid((string) microtime(true), true)), 0, 8));
        } while (Driver::where('nid', $nid)->exists());

        return $nid;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Driver $driver)
    {
        $validated = $request->validate([
            'nid' => ['nullable', 'string', 'max:20', 'unique:drivers,nid,'.$driver->id],
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

        $oldEmail = $driver->email;
        $driver->update($validated);

        // Update corresponding User record
        $user = User::where('email', $oldEmail)->first();
        if ($user) {
            $nameParts = explode(' ', $validated['name'], 2);
            $firstname = $nameParts[0];
            $lastname = $nameParts[1] ?? '';

            $userData = [
                'firstname' => $firstname,
                'lastname' => $lastname,
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'status' => $validated['status'],
                'nid' => $validated['nid'],
                'role' => 'driver',
            ];

            if (! empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
            }

            $user->update($userData);
        }

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
