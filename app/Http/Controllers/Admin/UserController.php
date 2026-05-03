<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('Admin/Users/Index', [
            'users' => User::query()
                ->when($request->search, function ($query, $search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('firstname', 'like', "%{$search}%")
                            ->orWhere('lastname', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
                })
                ->when($request->role, function ($query, $role) {
                    $query->where('role', $role);
                })
                ->latest()
                ->paginate(10)
                ->withQueryString(),
            'filters' => $request->only(['search', 'role']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:50', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'string', 'in:admin,driver'],
            'status' => ['required', 'string', 'in:active,inactive'],
        ]);

        return DB::transaction(function () use ($validated) {
            $validated['password'] = Hash::make($validated['password']);
            $user = User::create($validated);

            // If created as driver, also sync to drivers table
            if ($user->role === 'driver') {
                Driver::create([
                    'name' => $user->firstname.' '.$user->lastname,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'password' => $user->password,
                    'status' => $user->status,
                    'nid' => 'DRV-'.strtoupper(substr(md5(uniqid()), 0, 8)),
                ]);
            }

            return redirect()->back()->with('success', 'User created successfully.');
        });
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['required', 'string', 'max:50', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', 'string', 'in:admin,driver'],
            'status' => ['required', 'string', 'in:active,inactive'],
        ]);

        return DB::transaction(function () use ($validated, $user) {
            $oldEmail = $user->email;

            if (! empty($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            } else {
                unset($validated['password']);
            }

            $user->update($validated);

            // Sync to drivers table if role is driver
            if ($user->role === 'driver') {
                $driver = Driver::where('email', $oldEmail)->first();
                $driverData = [
                    'name' => $user->firstname.' '.$user->lastname,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'status' => $user->status,
                ];
                if (isset($validated['password'])) {
                    $driverData['password'] = $validated['password'];
                }

                if ($driver) {
                    $driver->update($driverData);
                } else {
                    $driverData['nid'] = 'DRV-'.strtoupper(substr(md5(uniqid()), 0, 8));
                    Driver::create($driverData);
                }
            } else {
                // If role changed from driver to admin, should we delete from drivers table?
                // For now, let's keep it but maybe deactivate?
                Driver::where('email', $oldEmail)->update(['status' => 'inactive']);
            }

            return redirect()->back()->with('success', 'User updated successfully.');
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'You cannot delete your own account.');
        }

        DB::transaction(function () use ($user) {
            if ($user->role === 'driver') {
                Driver::where('email', $user->email)->delete();
            }
            $user->delete();
        });

        return redirect()->back()->with('success', 'User deleted successfully.');
    }
}
