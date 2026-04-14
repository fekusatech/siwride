<?php

namespace Tests\Feature\Admin;

use App\Models\Driver;
use App\Models\Order;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ImportOrdersTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'email' => 'admin@example.com',
        ]);
    }

    #[Test]
    public function import_page_can_be_accessed()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/orders/import');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Orders/Import')
        );
    }

    #[Test]
    public function template_can_be_downloaded()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/orders/import/template');

        $response->assertStatus(200);
        $response->assertHeader('content-disposition', 'attachment; filename=orders-template.xlsx');
    }

    #[Test]
    public function import_requires_file()
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/orders/import', []);

        $response->assertSessionHasErrors('file');
    }

    #[Test]
    public function import_rejects_invalid_file_types()
    {
        $file = UploadedFile::fake()->create('orders.txt', 100);

        $response = $this->actingAs($this->admin)
            ->post('/admin/orders/import', ['file' => $file]);

        $response->assertSessionHasErrors('file');
    }

    #[Test]
    public function import_rejects_files_larger_than_10mb()
    {
        $file = UploadedFile::fake()->create('orders.xlsx', 11 * 1024); // 11MB

        $response = $this->actingAs($this->admin)
            ->post('/admin/orders/import', ['file' => $file]);

        $response->assertSessionHasErrors('file');
    }

    #[Test]
    public function orders_can_be_imported_from_excel()
    {
        $filePath = storage_path('test-orders-simple.xlsx');

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        // Headers
        $headers = ['Date', 'Time', 'Customer Name', 'Customer Phone', 'Pickup Address', 'Dropoff Address', 'Price'];
        foreach ($headers as $i => $header) {
            $sheet->setCellValueByColumnAndRow($i + 1, 1, $header);
        }

        // Data row
        $sheet->setCellValueByColumnAndRow(1, 2, '2026-03-30');
        $sheet->setCellValueByColumnAndRow(2, 2, '08:00');
        $sheet->setCellValueByColumnAndRow(3, 2, 'Simple Test');
        $sheet->setCellValueByColumnAndRow(4, 2, '081111111111');
        $sheet->setCellValueByColumnAndRow(5, 2, 'Pickup Location');
        $sheet->setCellValueByColumnAndRow(6, 2, 'Dropoff Location');
        $sheet->setCellValueByColumnAndRow(7, 2, '125000');

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        $file = new UploadedFile($filePath, 'orders.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $response = $this->actingAs($this->admin)
            ->post('/admin/orders/import', ['file' => $file]);

        $response->assertRedirect('/admin/orders');
        $response->assertSessionHas('success', 'Orders imported successfully!');

        $this->assertDatabaseHas('orders', [
            'customer_name' => 'Simple Test',
            'customer_phone' => '081111111111',
        ]);

        // Cleanup
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    #[Test]
    public function import_creates_orders_with_required_fields()
    {
        // Create a real Excel file for testing
        $filePath = storage_path('test-orders.xlsx');

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        // Headers
        $headers = ['Date', 'Time', 'Customer Name', 'Customer Phone', 'Pickup Address', 'Dropoff Address', 'Price'];
        foreach ($headers as $i => $header) {
            $sheet->setCellValueByColumnAndRow($i + 1, 1, $header);
        }

        // Data row
        $sheet->setCellValueByColumnAndRow(1, 2, '2026-03-30');
        $sheet->setCellValueByColumnAndRow(2, 2, '08:00');
        $sheet->setCellValueByColumnAndRow(3, 2, 'John Doe');
        $sheet->setCellValueByColumnAndRow(4, 2, '081234567890');
        $sheet->setCellValueByColumnAndRow(5, 2, 'Airport Terminal 1');
        $sheet->setCellValueByColumnAndRow(6, 2, 'Hotel Grand Bali');
        $sheet->setCellValueByColumnAndRow(7, 2, '150000');

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        $file = new UploadedFile($filePath, 'orders.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $response = $this->actingAs($this->admin)
            ->post('/admin/orders/import', ['file' => $file]);

        $response->assertRedirect('/admin/orders');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('orders', [
            'customer_name' => 'John Doe',
            'customer_phone' => '081234567890',
            'pickup_address' => 'Airport Terminal 1',
            'dropoff_address' => 'Hotel Grand Bali',
            'price' => 150000,
        ]);

        // Cleanup
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    #[Test]
    public function import_assigns_driver_by_code()
    {
        $driver = Driver::factory()->create(['nid' => 'siw01', 'name' => 'Rahman']);

        $filePath = storage_path('test-orders-driver.xlsx');

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        // Headers
        $headers = ['Date', 'Time', 'Customer Name', 'Customer Phone', 'Driver Code', 'Pickup Address', 'Dropoff Address', 'Price'];
        foreach ($headers as $i => $header) {
            $sheet->setCellValueByColumnAndRow($i + 1, 1, $header);
        }

        // Data row with driver code
        $sheet->setCellValueByColumnAndRow(1, 2, '2026-03-30');
        $sheet->setCellValueByColumnAndRow(2, 2, '08:00');
        $sheet->setCellValueByColumnAndRow(3, 2, 'John Doe');
        $sheet->setCellValueByColumnAndRow(4, 2, '081234567890');
        $sheet->setCellValueByColumnAndRow(5, 2, 'siw01');
        $sheet->setCellValueByColumnAndRow(6, 2, 'Airport Terminal 1');
        $sheet->setCellValueByColumnAndRow(7, 2, 'Hotel Grand Bali');
        $sheet->setCellValueByColumnAndRow(8, 2, '150000');

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        $file = new UploadedFile($filePath, 'orders.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $response = $this->actingAs($this->admin)
            ->post('/admin/orders/import', ['file' => $file]);

        $response->assertRedirect('/admin/orders');

        $this->assertDatabaseHas('orders', [
            'driver_id' => $driver->id,
            'customer_name' => 'John Doe',
        ]);

        // Cleanup
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    #[Test]
    public function import_assigns_vehicle_by_plate()
    {
        $vehicle = Vehicle::factory()->create(['registration_number' => 'DK 1234 AB']);

        $filePath = storage_path('test-orders-vehicle.xlsx');

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        // Headers
        $headers = ['Date', 'Time', 'Customer Name', 'Customer Phone', 'Vehicle Plate', 'Pickup Address', 'Dropoff Address', 'Price'];
        foreach ($headers as $i => $header) {
            $sheet->setCellValueByColumnAndRow($i + 1, 1, $header);
        }

        // Data row with vehicle plate
        $sheet->setCellValueByColumnAndRow(1, 2, '2026-03-30');
        $sheet->setCellValueByColumnAndRow(2, 2, '08:00');
        $sheet->setCellValueByColumnAndRow(3, 2, 'Jane Smith');
        $sheet->setCellValueByColumnAndRow(4, 2, '081234567891');
        $sheet->setCellValueByColumnAndRow(5, 2, 'DK 1234 AB');
        $sheet->setCellValueByColumnAndRow(6, 2, 'Airport Terminal 2');
        $sheet->setCellValueByColumnAndRow(7, 2, 'Ubud Resort');
        $sheet->setCellValueByColumnAndRow(8, 2, '200000');

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        $file = new UploadedFile($filePath, 'orders.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $response = $this->actingAs($this->admin)
            ->post('/admin/orders/import', ['file' => $file]);

        $response->assertRedirect('/admin/orders');

        $this->assertDatabaseHas('orders', [
            'vehicle_id' => $vehicle->id,
            'customer_name' => 'Jane Smith',
        ]);

        // Cleanup
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    #[Test]
    public function import_generates_booking_code_if_empty()
    {
        $filePath = storage_path('test-orders-autogen.xlsx');

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        // Headers
        $headers = ['Date', 'Time', 'Customer Name', 'Customer Phone', 'Pickup Address', 'Dropoff Address', 'Price'];
        foreach ($headers as $i => $header) {
            $sheet->setCellValueByColumnAndRow($i + 1, 1, $header);
        }

        // Data row
        $sheet->setCellValueByColumnAndRow(1, 2, '2026-03-30');
        $sheet->setCellValueByColumnAndRow(2, 2, '08:00');
        $sheet->setCellValueByColumnAndRow(3, 2, 'Test User');
        $sheet->setCellValueByColumnAndRow(4, 2, '081234567892');
        $sheet->setCellValueByColumnAndRow(5, 2, 'Airport Terminal 3');
        $sheet->setCellValueByColumnAndRow(6, 2, 'Test Hotel');
        $sheet->setCellValueByColumnAndRow(7, 2, '175000');

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        $file = new UploadedFile($filePath, 'orders.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $response = $this->actingAs($this->admin)
            ->post('/admin/orders/import', ['file' => $file]);

        $response->assertRedirect('/admin/orders');

        // Check that booking code was auto-generated (starts with ORD)
        $order = Order::where('customer_name', 'Test User')->first();
        $this->assertNotNull($order);
        $this->assertStringStartsWith('ORD', $order->booking_code);
        $this->assertStringStartsWith('SIW', $order->order_number);

        // Cleanup
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
}
