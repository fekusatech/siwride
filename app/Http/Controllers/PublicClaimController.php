<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Driver;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PublicClaimController extends Controller
{
    protected WhatsAppService $waService;

    public function __construct(WhatsAppService $waService)
    {
        $this->waService = $waService;
    }

    public function show($booking_code)
    {
        $order = Order::select('id', 'booking_code', 'date', 'time', 'pickup_address', 'dropoff_address', 'passengers', 'flight_number', 'price', 'parking_gas_fee', 'driver_id')
            ->where('booking_code', $booking_code)
            ->firstOrFail();

        // Retrieve the secure customer details from the session if the driver has just authenticated
        $claimedData = session()->pull('claimed_order_' . $order->id);

        return Inertia::render('Public/ClaimOrder', [
            'order' => $order,
            'isAssigned' => $order->driver_id !== null,
            'claimedData' => $claimedData
        ]);
    }

    public function store($booking_code, Request $request)
    {
        $request->validate([
            'nid' => 'required|string'
        ]);

        $order = Order::where('booking_code', $booking_code)->firstOrFail();
        
        $driver = Driver::where('nid', $request->nid)->first();
        if (!$driver) {
            return back()->withErrors(['nid' => 'NID tidak valid atau tidak ditemukan.']);
        }

        // Check if order is already taken by a DIFFERENT driver
        if ($order->driver_id && $order->driver_id !== $driver->id) {
            return back()->with('error', 'Maaf, Order ini sudah diambil oleh driver lain.');
        }

        // Assign to this driver if it's currently unassigned
        if (!$order->driver_id) {
            $order->update([
                'driver_id' => $driver->id,
            ]);
        }

        // Store the sensitive details temporarily in session to display them securely on the next view
        session()->put('claimed_order_' . $order->id, [
            'customer_name' => $order->customer_name,
            'customer_phone' => $order->customer_phone,
            'driver_name' => $driver->name,
        ]);

        // Send confirmation and details via WhatsApp API to the driver
        $privateMessage = "*ORDER BERHASIL DIKLAIM*\n\n" .
            "Booking Code: {$order->booking_code}\n".
            "Customer: {$order->customer_name}\n" .
            "Customer Phone: {$order->customer_phone}\n\n" .
            "Pickup: {$order->pickup_address}\n" .
            "Dropoff: {$order->dropoff_address}\n" .
            "Date: {$order->date}\nTime: {$order->time}\n\n" .
            "Tugas Anda telah dimulai. Jangan lupa konfirmasi ke Pelanggan!";

        $this->waService->sendPrivateMessage($driver->phone, $privateMessage);

        return redirect()->route('orders.claim.show', $booking_code)->with('success', 'Order berhasil diklaim!');
    }
}
