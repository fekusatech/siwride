<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\OrdersImport;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Order;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('Admin/Orders/Index', [
            'orders' => Order::with(['driver', 'vehicle', 'claimedDriver', 'customer'])
                ->when($request->search, function ($query, $search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('booking_code', 'like', "%{$search}%")
                            ->orWhere('order_number', 'like', "%{$search}%")
                            ->orWhere('customer_name', 'like', "%{$search}%")
                            ->orWhereHas('customer', function ($cq) use ($search) {
                                $cq->where('name', 'like', "%{$search}%");
                            })
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
            'orders' => Order::with(['driver', 'vehicle', 'customer'])->get(),
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
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'customer_email' => ['required', 'email', 'max:255'],
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

        $customer = Customer::firstOrCreate(
            ['email' => $validated['customer_email']],
            [
                'name' => $validated['customer_name'],
                'phone' => $validated['customer_phone'] ?? null,
            ]
        );

        Order::create(array_merge(
            $validated,
            ['customer_id' => $customer->id]
        ));

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
        try {
            $validated = $request->validate([
                'booking_code' => ['required', 'string', 'unique:orders,booking_code,'.$order->id],
                'order_number' => ['required', 'string'],
                'date' => ['required', 'date'],
                'time' => ['required'],
                'customer_name' => ['required', 'string', 'max:255'],
                'customer_phone' => ['nullable', 'string', 'max:50'],
                'customer_email' => ['required', 'email', 'max:255'],
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

            $customer = Customer::firstOrCreate(
                ['email' => $validated['customer_email']],
                [
                    'name' => $validated['customer_name'],
                    'phone' => $validated['customer_phone'] ?? null,
                ]
            );

            $order->update(array_merge(
                $validated,
                ['customer_id' => $customer->id]
            ));

            return redirect()->back()
                ->with('success', 'Order updated successfully.');
        } catch (ValidationException $e) {
            Log::error('Validation error on order update: '.json_encode($e->errors()));
            throw $e; // Re-throw to let Inertia handle it and display errors in form
        } catch (\Exception $e) {
            Log::error('Error updating order: '.$e->getMessage());

            return redirect()->back()->with('error', 'Error updating order: '.$e->getMessage());
        }
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
        Log::info("OrderController: Attempting SHARE for order ID: {$order->id}");

        $validated = $request->validate([
            'message' => ['required', 'string'],
        ]);

        $response = $waService->sendGroupMessage($validated['message']);

        if ($response && $response->successful()) {
            Log::info("OrderController: SHARE successful for order ID: {$order->id}");

            return redirect()->back()->with('success', 'Order berhasil dibagikan ke Grup WhatsApp!');
        }

        Log::warning("OrderController: SHARE FAILED for order ID: {$order->id}");

        return redirect()->back()->with('error', 'Gagal mengirim pesan ke WhatsApp. Coba lagi nanti.');
    }

    /**
     * Accept a driver's claim request and assign the driver to the order.
     */
    public function acceptClaim(Order $order, WhatsAppService $waService)
    {
        if (! $order->claimed_driver_id) {
            return redirect()->back()->with('error', 'Tidak ada claim yang perlu dikonfirmasi.');
        }

        $driver = Driver::find($order->claimed_driver_id);
        if (! $driver) {
            return redirect()->back()->with('error', 'Driver tidak ditemukan.');
        }

        $order->update([
            'driver_id' => $order->claimed_driver_id,
            'claimed_driver_id' => null,
        ]);

        $d = new \DateTime($order->date);
        $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $dateStr = $d->format('j').' '.$months[(int) $d->format('n') - 1].' '.$d->format('Y');
        $timeStr = $order->time ? substr($order->time, 0, 5) : '';
        $distance = $this->getDistance($order);
        $flightNumber = $order->flight_number ?: '-';

        $vehicle = $driver->vehicles()->first();
        $vehicleInfo = $vehicle ? "{$vehicle->brand} {$vehicle->model} ({$vehicle->registration_number})" : '-';

        $privateMessage = "ORDER DIKONFIRMASI ADMIN\n\n".
            "Booking Code: {$order->booking_code}\n".
            "Order Number: {$order->order_number}\n\n".
            "Driver:\n".
            "Nama: {$driver->name}\n".
            "Mobil: {$vehicleInfo}\n\n".
            "Customer:\n".
            "Nama: {$order->customer_name}\n".
            "Telepon: {$order->customer_phone}\n".
            "Flight Number: {$flightNumber}\n\n".
            "Pickup: {$order->pickup_address}\n\n".
            "Dropoff: {$order->dropoff_address}\n\n".
            ($distance !== '-' ? "Jarak: {$distance}\n" : '').
            "Tanggal: {$dateStr}\n".
            "Jam: {$timeStr} WITA\n".
            "Penumpang: {$order->passengers} Pax\n".
            'Harga: Rp '.number_format($order->price, 0, ',', '.')."\n\n".
            'Silakan hubungi customer untuk konfirmasi penjemputan!';

        $waService->sendPrivateMessage($driver->phone, $privateMessage);

        return redirect()->back()->with('success', 'Claim dikonfirmasi! Detail customer telah dikirim ke driver.');
    }

    /**
     * Reject a driver's claim request.
     */
    public function rejectClaim(Order $order)
    {
        if (! $order->claimed_driver_id) {
            return redirect()->back()->with('error', 'Tidak ada claim yang perlu ditolak.');
        }

        $order->update([
            'claimed_driver_id' => null,
        ]);

        return redirect()->back()->with('success', 'Claim driver ditolak.');
    }

    /**
     * Re-send order details via WhatsApp to the assigned driver.
     */
    public function resendWaToDriver(Order $order, WhatsAppService $waService)
    {
        if (! $order->driver_id) {
            return redirect()->back()->with('error', 'Order belum memiliki driver.');
        }

        $driver = Driver::find($order->driver_id);
        if (! $driver) {
            return redirect()->back()->with('error', 'Driver tidak ditemukan.');
        }

        $d = new \DateTime($order->date);
        $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $dateStr = $d->format('j').' '.$months[(int) $d->format('n') - 1].' '.$d->format('Y');
        $timeStr = $order->time ? substr($order->time, 0, 5) : '';
        $distance = $this->getDistance($order);
        $flightNumber = $order->flight_number ?: '-';

        $vehicle = $driver->vehicles()->first();
        $vehicleInfo = $vehicle ? "{$vehicle->brand} {$vehicle->model} ({$vehicle->registration_number})" : '-';

        $privateMessage = "ORDER DIKONFIRMASI ADMIN\n\n".
            "Booking Code: {$order->booking_code}\n".
            "Order Number: {$order->order_number}\n\n".
            "Driver:\n".
            "Nama: {$driver->name}\n".
            "Mobil: {$vehicleInfo}\n\n".
            "Customer:\n".
            "Nama: {$order->customer_name}\n".
            "Telepon: {$order->customer_phone}\n".
            "Flight Number: {$flightNumber}\n\n".
            "Pickup: {$order->pickup_address}\n\n".
            "Dropoff: {$order->dropoff_address}\n\n".
            ($distance !== '-' ? "Jarak: {$distance}\n" : '').
            "Tanggal: {$dateStr}\n".
            "Jam: {$timeStr} WITA\n".
            "Penumpang: {$order->passengers} Pax\n".
            'Harga: Rp '.number_format($order->price, 0, ',', '.')."\n\n".
            'Silakan hubungi customer untuk konfirmasi penjemputan!';

        $response = $waService->sendPrivateMessage($driver->phone, $privateMessage);

        if ($response && $response->successful()) {
            return redirect()->back()->with('success', 'Detail order berhasil dikirim ulang ke driver.');
        }

        return redirect()->back()->with('error', 'Gagal mengirim pesan ke driver.');
    }

    /**
     * Get distance between pickup and dropoff via Google Distance Matrix API.
     * Called from frontend to avoid CORS issues.
     */
    public function distanceApi(Request $request)
    {
        $request->validate([
            'pickup_latitude' => 'required|numeric',
            'pickup_longitude' => 'required|numeric',
            'dropoff_latitude' => 'required|numeric',
            'dropoff_longitude' => 'required|numeric',
        ]);

        $apiKey = config('services.google.maps_api_key');
        if (! $apiKey) {
            return response()->json(['distance' => '-']);
        }

        $origins = "{$request->pickup_latitude},{$request->pickup_longitude}";
        $destinations = "{$request->dropoff_latitude},{$request->dropoff_longitude}";

        try {
            $response = Http::get('https://maps.googleapis.com/maps/api/distancematrix/json', [
                'origins' => $origins,
                'destinations' => $destinations,
                'mode' => 'driving',
                'key' => $apiKey,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $distance = $data['rows'][0]['elements'][0]['distance']['text'] ?? '-';

                return response()->json(['distance' => $distance]);
            }
        } catch (\Exception $e) {
            Log::warning("Failed to get distance from Google API: {$e->getMessage()}");
        }

        return response()->json(['distance' => '-']);
    }

    /**
     * Calculate driving distance between pickup and dropoff using Google Distance Matrix API.
     */
    private function getDistance(Order $order): string
    {
        if (! $order->pickup_latitude || ! $order->pickup_longitude || ! $order->dropoff_latitude || ! $order->dropoff_longitude) {
            return '-';
        }

        $apiKey = config('services.google.maps_api_key');
        if (! $apiKey) {
            return '-';
        }

        $origins = "{$order->pickup_latitude},{$order->pickup_longitude}";
        $destinations = "{$order->dropoff_latitude},{$order->dropoff_longitude}";

        try {
            $response = Http::get('https://maps.googleapis.com/maps/api/distancematrix/json', [
                'origins' => $origins,
                'destinations' => $destinations,
                'mode' => 'driving',
                'key' => $apiKey,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['rows'][0]['elements'][0]['distance']['text'])) {
                    return $data['rows'][0]['elements'][0]['distance']['text'];
                }
            }
        } catch (\Exception $e) {
            Log::warning("Failed to get distance from Google API: {$e->getMessage()}");
        }

        return '-';
    }

    /**
     * Show import orders page
     */
    public function importPage(): Response
    {
        return Inertia::render('Admin/Orders/Import');
    }

    /**
     * Handle Excel file upload and import
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'], // 10MB max
        ]);

        try {
            Excel::import(new OrdersImport, $request->file('file'));

            return redirect()->route('admin.orders.index')
                ->with('success', 'Orders imported successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to import orders: '.$e->getMessage());
        }
    }

    /**
     * Download Excel template for import
     */
    public function downloadTemplate(): BinaryFileResponse
    {
        $fileName = 'orders-template.xlsx';
        $filePath = storage_path('app/templates/'.$fileName);

        // Create template if it doesn't exist
        if (! file_exists($filePath)) {
            $this->createTemplate($filePath);
        }

        return response()->download($filePath, $fileName);
    }

    /**
     * Create Excel template for orders import
     */
    private function createTemplate(string $filePath): void
    {
        $directory = dirname($filePath);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $headers = [
            'Date',
            'Time',
            'Customer Name',
            'Customer Phone',
            'Flight Number',
            'Driver Code',
            'Vehicle Plate',
            'Pickup Address',
            'Dropoff Address',
            'Passengers',
            'Price',
            'Parking Gas Fee',
            'Status',
            'Booking Code',
            'Order Number',
        ];

        foreach ($headers as $index => $header) {
            $sheet->setCellValueByColumnAndRow($index + 1, 1, $header);
        }

        // Style header row
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '366092']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];

        for ($i = 1; $i <= count($headers); $i++) {
            $sheet->getStyleByColumnAndRow($i, 1)->applyFromArray($headerStyle);
        }

        // Add sample data row
        $sampleData = [
            '2026-03-30',
            '08:00',
            'John Doe',
            '081234567890',
            'GA123',
            'siw01',
            'DK 1234 AB',
            'Airport Terminal 1',
            'Hotel Grand Bali',
            '2',
            '150000',
            '20000',
            'pending',
            '',
            '',
        ];

        foreach ($sampleData as $index => $value) {
            $sheet->setCellValueByColumnAndRow($index + 1, 2, $value);
        }

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(12);
        $sheet->getColumnDimension('B')->setWidth(10);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(18);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(15);
        $sheet->getColumnDimension('H')->setWidth(25);
        $sheet->getColumnDimension('I')->setWidth(25);
        $sheet->getColumnDimension('J')->setWidth(12);
        $sheet->getColumnDimension('K')->setWidth(12);
        $sheet->getColumnDimension('L')->setWidth(18);
        $sheet->getColumnDimension('M')->setWidth(12);
        $sheet->getColumnDimension('N')->setWidth(18);
        $sheet->getColumnDimension('O')->setWidth(15);

        // Save template
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);
    }
}
