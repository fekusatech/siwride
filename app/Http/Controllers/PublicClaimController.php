<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Order;
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
        $order = Order::select('id', 'booking_code', 'date', 'time', 'pickup_address', 'dropoff_address', 'passengers', 'flight_number', 'price', 'parking_gas_fee', 'driver_id', 'claimed_driver_id')
            ->where('booking_code', $booking_code)
            ->firstOrFail();

        $claimedData = session()->pull('claimed_order_'.$order->id);

        return Inertia::render('Public/ClaimOrder', [
            'order' => $order,
            'isAssigned' => $order->driver_id !== null,
            'isClaimPending' => $order->claimed_driver_id !== null && $order->driver_id === null,
            'claimedData' => $claimedData,
        ]);
    }

    public function store($booking_code, Request $request)
    {
        $request->validate([
            'nid' => 'required|string',
        ]);

        $order = Order::where('booking_code', $booking_code)->firstOrFail();

        $driver = Driver::where('nid', $request->nid)->first();
        if (! $driver) {
            return back()->withErrors(['nid' => 'NID tidak valid atau tidak ditemukan.']);
        }

        if ($order->driver_id) {
            return back()->with('error', 'Maaf, Order ini sudah dikonfirmasi oleh admin.');
        }

        if ($order->claimed_driver_id && $order->claimed_driver_id !== $driver->id) {
            return back()->with('error', 'Maaf, Order ini sedang menunggu konfirmasi dari driver lain.');
        }

        $order->update([
            'claimed_driver_id' => $driver->id,
        ]);

        session()->put('claimed_order_'.$order->id, [
            'customer_name' => $order->customer_name,
            'customer_phone' => $order->customer_phone,
            'driver_name' => $driver->name,
        ]);

        return redirect()->route('orders.claim.show', $booking_code)->with('success', 'Claim berhasil dikirim! Menunggu konfirmasi admin.');
    }
}
