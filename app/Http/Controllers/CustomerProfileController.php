<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class CustomerProfileController extends Controller
{
    /**
     * Show the customer profile page with order history.
     */
    public function index()
    {
        $customer = Auth::guard('customer')->user();
        
        $orders = \App\Models\Order::where('customer_id', $customer->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('customer/profile', [
            'orders' => $orders,
        ]);
    }

    /**
     * Update customer profile details.
     */
    public function update(Request $request)
    {
        /** @var \App\Models\Customer $customer */
        $customer = Auth::guard('customer')->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $data = [
            'name' => $validated['name'],
            'phone' => $validated['phone'],
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $customer->update($data);

        return back()->with('success', 'Profile updated successfully.');
    }
}
