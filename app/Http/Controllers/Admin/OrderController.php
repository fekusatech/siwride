<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Order;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('Admin/Orders/Index', [
            'orders' => Order::with(['driver', 'vehicle'])
                ->when($request->search, function ($query, $search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('customer_name', 'like', "%{$search}%")
                            ->orWhere('booking_code', 'like', "%{$search}%")
                            ->orWhere('order_number', 'like', "%{$search}%")
                            ->orWhereHas('driver', function ($dq) use ($search) {
                                $dq->where('name', 'like', "%{$search}%");
                            });
                    });
                })
                ->when($request->status, function ($query, $status) {
                    $query->where('status', $status);
                })
                ->when($request->driver_id, function ($query, $driverId) {
                    $query->where('driver_id', $driverId);
                })
                ->when($request->from_date, function ($query, $fromDate) {
                    $query->whereDate('date', '>=', $fromDate);
                })
                ->when($request->to_date, function ($query, $toDate) {
                    $query->whereDate('date', '<=', $toDate);
                })
                ->latest()
                ->paginate(10)
                ->withQueryString(),
            'filters' => $request->only(['search', 'status', 'driver_id', 'from_date', 'to_date']),
            'drivers' => Driver::with('vehicles')->get(),
            'google_maps_api_key' => config('services.google.maps_api_key'),
        ]);
    }

    /**
     * Display the calendar view of the resource.
     */
    public function calendar(Request $request): Response
    {
        return Inertia::render('Admin/Orders/Calendar', [
            'orders' => Order::with(['driver', 'vehicle'])->get(),
            'drivers' => Driver::with('vehicles')->get(),
            'google_maps_api_key' => config('services.google.maps_api_key'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Orders/Create', [
            'drivers' => Driver::with('vehicles')->get(),
            'google_maps_api_key' => config('services.google.maps_api_key'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'booking_code' => ['required', 'string', 'unique:orders'],
            'order_number' => ['required', 'string'],
            'date' => ['required', 'date'],
            'time' => ['required'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:50'],
            'flight_number' => ['nullable', 'string', 'max:50'],
            'driver_id' => ['nullable', 'exists:drivers,id'],
            'vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'pickup_address' => ['required', 'string'],
            'pickup_latitude' => ['nullable', 'numeric'],
            'pickup_longitude' => ['nullable', 'numeric'],
            'dropoff_address' => ['required', 'string'],
            'dropoff_latitude' => ['nullable', 'numeric'],
            'dropoff_longitude' => ['nullable', 'numeric'],
            'passengers' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'numeric', 'min:0'],
            'parking_gas_fee' => ['required', 'numeric', 'min:0'],
        ]);

        Order::create($validated);

        return redirect()->route('admin.orders.index')
            ->with('success', 'Order created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'booking_code' => ['required', 'string', 'unique:orders,booking_code,'.$order->id],
            'order_number' => ['required', 'string'],
            'date' => ['required', 'date'],
            'time' => ['required'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:50'],
            'flight_number' => ['nullable', 'string', 'max:50'],
            'driver_id' => ['nullable', 'exists:drivers,id'],
            'vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'pickup_address' => ['required', 'string'],
            'pickup_latitude' => ['nullable', 'numeric'],
            'pickup_longitude' => ['nullable', 'numeric'],
            'dropoff_address' => ['required', 'string'],
            'dropoff_latitude' => ['nullable', 'numeric'],
            'dropoff_longitude' => ['nullable', 'numeric'],
            'passengers' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'numeric', 'min:0'],
            'parking_gas_fee' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'string', 'in:pending,completed,cancelled'],
        ]);

        $order->update($validated);

        return redirect()->back()
            ->with('success', 'Order updated successfully.');
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,completed,cancelled'],
        ]);

        $order->update(['status' => $validated['status']]);

        return redirect()->back()
            ->with('success', 'Order status updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        $order->delete();

        return redirect()->back()
            ->with('success', 'Order deleted successfully.');
    }

    /**
     * Share the order details directly to the WhatsApp Group via API.
     */
    public function share(Request $request, Order $order, WhatsAppService $waService)
    {
        \Illuminate\Support\Facades\Log::info("OrderController: Attempting SHARE for order ID: {$order->id}");

        $validated = $request->validate([
            'message' => ['required', 'string']
        ]);

        $response = $waService->sendGroupMessage($validated['message']);

        if ($response && $response->successful()) {
            \Illuminate\Support\Facades\Log::info("OrderController: SHARE successful for order ID: {$order->id}");
            return redirect()->back()->with('success', 'Order berhasil dibagikan ke Grup WhatsApp!');
        }

        \Illuminate\Support\Facades\Log::warning("OrderController: SHARE FAILED for order ID: {$order->id}");
        return redirect()->back()->with('error', 'Gagal mengirim pesan ke WhatsApp. Coba lagi nanti.');
    }
}
