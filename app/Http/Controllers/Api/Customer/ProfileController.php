<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Customer\BookingResource;
use App\Models\Customer;
use App\Services\OrderCancellationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        /** @var Customer $customer */
        $customer = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $data = [
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
        ];

        if (! empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $customer->update($data);

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
            ],
        ]);
    }

    /**
     * All transfer bookings for the authenticated customer, newest first —
     * the app-side equivalent of CustomerProfileController's order history.
     */
    public function orders(Request $request, OrderCancellationService $cancellations)
    {
        /** @var Customer $customer */
        $customer = $request->user();

        $orders = $customer->orders()
            ->where('is_return_trip', false)
            ->with(['vehicleCategory', 'driver', 'linkedOrder.vehicleCategory'])
            ->orderByDesc('created_at')
            ->get();

        $orders->each(fn ($order) => $cancellations->autoCancelIfEligible($order));

        return response()->json([
            'status' => 'success',
            'data' => BookingResource::collection($orders),
        ]);
    }
}
