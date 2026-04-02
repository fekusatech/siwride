# Developer Quick Reference: Excel Import Feature

## 🎯 Quick Start for Developers

### Routes
```php
GET    /admin/orders/import              → importPage()
POST   /admin/orders/import              → import()
GET    /admin/orders/import/template     → downloadTemplate()
```

### Key Files
- **Controller**: `app/Http/Controllers/Admin/OrderController.php`
- **Import Class**: `app/Imports/OrdersImport.php`
- **Frontend**: `resources/js/pages/Admin/Orders/Import.svelte`
- **Tests**: `tests/Feature/Admin/ImportOrdersTest.php`

---

## 🔧 Controller Methods

### importPage()
```php
public function importPage(): Response
{
    return Inertia::render('Admin/Orders/Import');
}
```
Returns the import page component.

### import(Request $request)
```php
public function import(Request $request)
{
    $request->validate([
        'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
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
```
Handles file upload and imports orders.

### downloadTemplate()
```php
public function downloadTemplate(): BinaryFileResponse
{
    $fileName = 'orders-template.xlsx';
    $filePath = storage_path('app/templates/'.$fileName);

    if (!file_exists($filePath)) {
        $this->createTemplate($filePath);
    }

    return response()->download($filePath, $fileName);
}
```
Downloads or generates template file.

### createTemplate()
```php
private function createTemplate(string $filePath): void
{
    // Creates storage/app/templates/orders-template.xlsx
    // With proper headers, sample data, and styling
}
```
Generates Excel template dynamically.

---

## 📊 OrdersImport Class

Located: `app/Imports/OrdersImport.php`

### Key Methods
- `rules()` - Validation rules for imported data
- `model(array $row)` - Transform each row to Order model
- `parseDate()` - Parse date in multiple formats
- `parseTime()` - Parse time in multiple formats
- `batchSize()` - Returns 100 (batch insert size)

### Example Usage
```php
use App\Imports\OrdersImport;
use Maatwebsite\Excel\Facades\Excel;

Excel::import(new OrdersImport, 'path/to/file.xlsx');
```

---

## 🎨 Frontend Component

File: `resources/js/pages/Admin/Orders/Import.svelte`

### Key Variables
```javascript
let selectedFile: File | null = null;
let isImporting: boolean = false;
let importError: string = '';
let dragActive: boolean = false;
```

### Key Functions
- `handleDragEnter()` - Enable drag area
- `handleDrop()` - Handle file drop
- `handleFileSelect()` - Handle file selection
- `handleImport()` - Submit import request
- `downloadTemplate()` - Download template
- `resetForm()` - Reset form state

### Import Request
```javascript
router.post('/admin/orders/import', formData, {
    onFinish: () => { isImporting = false; },
    onError: (errors) => { importError = errors.file[0]; }
});
```

---

## 🧪 Testing

### Run All Tests
```bash
php artisan test tests/Feature/Admin/ImportOrdersTest.php
```

### Run Specific Test
```bash
php artisan test tests/Feature/Admin/ImportOrdersTest.php --filter=import_page_can_be_accessed
```

### Test Template
```php
#[\PHPUnit\Framework\Attributes\Test]
public function test_name()
{
    $response = $this->actingAs($this->admin)
        ->post('/admin/orders/import', ['file' => $file]);
    
    $response->assertRedirect('/admin/orders');
    $response->assertSessionHas('success');
}
```

### Create Test File for Testing
```php
$filePath = storage_path('test-orders.xlsx');

$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Add headers
$headers = ['Date', 'Time', 'Customer Name', ...];
foreach ($headers as $i => $header) {
    $sheet->setCellValueByColumnAndRow($i + 1, 1, $header);
}

// Add data
$sheet->setCellValueByColumnAndRow(1, 2, '2026-03-30');
// ...

$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
$writer->save($filePath);

$file = new UploadedFile($filePath, 'orders.xlsx', 
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 
    null, true);
```

---

## 📦 Dependencies

### Composer
```bash
composer require maatwebsite/excel ^3.1
```

### Package Files
- `vendor/maatwebsite/excel/` - Main package
- `vendor/phpoffice/phpspreadsheet/` - Excel manipulation
- `vendor/maennchen/zipstream-php/` - Stream handling

---

## 🔍 Date/Time Parsing

### Supported Date Formats
```
YYYY-MM-DD          2026-03-30
DD/MM/YYYY          30/03/2026
DD-MM-YYYY          30-03-2026
MM/DD/YYYY          03/30/2026
Y/m/d               2026/3/30
DD F YYYY           30 March 2026
Excel numeric       45403 (internally converted)
```

### Supported Time Formats
```
HH:MM               08:00
HH:MM:SS            08:00:00
Excel numeric       0.333333 (internally converted)
```

---

## 🎯 Data Validation Rules

```php
[
    'date' => 'required|date',
    'time' => 'required',
    'customer_name' => 'required|string|max:255',
    'customer_phone' => 'required|string|max:50',
    'pickup_address' => 'required|string',
    'dropoff_address' => 'required|string',
    'passengers' => 'nullable|integer|min:1',
    'price' => 'required|numeric|min:0',
    'parking_gas_fee' => 'nullable|numeric|min:0',
    'status' => 'nullable|in:pending,completed,cancelled',
]
```

---

## 💾 Storage Paths

### Template Storage
```
storage/app/templates/orders-template.xlsx
```

### Creating Template Directory
```php
$directory = storage_path('app/templates');
if (!is_dir($directory)) {
    mkdir($directory, 0755, true);
}
```

---

## 🔗 Relationships & Lookups

### Driver Lookup
```php
$driver = Driver::where('nid', $driverCode)
    ->orWhere('name', 'like', "%{$driverCode}%")
    ->first();
$driverId = $driver?->id; // null if not found
```

### Vehicle Lookup
```php
$vehicle = Vehicle::where('registration_number', $vehicleId)
    ->orWhere('type', 'like', "%{$vehicleId}%")
    ->orWhere('model', 'like', "%{$vehicleId}%")
    ->first();
$vehicleId = $vehicle?->id; // null if not found
```

---

## 🎲 Auto-Generation

### Booking Code
```php
private function generateBookingCode(): string
{
    $prefix = 'ORD';
    $date = now()->format('Ymd');
    $random = strtoupper(substr(uniqid(), -6));
    return "{$prefix}{$date}{$random}"; // ORD20260330A1B2C3
}
```

### Order Number
```php
private function generateOrderNumber(): string
{
    $prefix = 'SIW';
    $date = now()->format('Ymd');
    $random = strtoupper(substr(uniqid(), -4));
    return "{$prefix}{$date}{$random}"; // SIW20260330X1Y2
}
```

---

## 🚨 Error Handling

### Try-Catch in Controller
```php
try {
    Excel::import(new OrdersImport, $request->file('file'));
    // Success
} catch (ValidationException $e) {
    // Validation failed
    return back()->withErrors($e->errors());
} catch (\Exception $e) {
    // General error
    return back()->with('error', $e->getMessage());
}
```

### Validation Exception Handling
```php
// In OrdersImport::rules()
public function rules(): array
{
    return [
        'date' => 'required|date',
        'time' => 'required',
        // ... other rules
    ];
}
```

---

## 📱 Frontend Error Display

```svelte
{#if importError}
    <div class="alert alert-danger">
        <strong>Error:</strong> {importError}
    </div>
{/if}
```

---

## 🔐 Security Checks

### File Validation
```php
$request->validate([
    'file' => [
        'required',           // File must exist
        'file',               // Must be uploaded file
        'mimes:xlsx,xls,csv', // MIME type check
        'max:10240'           // 10MB size limit
    ],
]);
```

### No SQL Injection
- All data goes through Eloquent
- No raw SQL queries
- Parameterized lookups

### Authorization
- Route middleware: `auth`
- Admin-only routes
- No role check needed (implicit in route group)

---

## 🧹 Cleanup

### After Import Test
```php
if (file_exists($filePath)) {
    unlink($filePath);
}
```

### Clear Template Cache
```bash
php artisan cache:clear
```

---

## 📊 Performance Tips

### Large File Import
```php
// Batch size: 100 rows per insert
public function batchSize(): int
{
    return 100;
}
```

### Memory Optimization
- Streaming read (not entire file in memory)
- Batch processing
- Automatic garbage collection

---

## 🐛 Debugging

### Enable Query Logging
```php
\DB::enableQueryLog();
// ... do import
dd(\DB::getQueryLog());
```

### Check Import Status
```php
// In tinker
Order::latest()->limit(10)->get();
Order::where('created_at', '>=', now()->subHours(1))->count();
```

### View Upload Directory
```bash
ls -la storage/app/templates/
```

---

## 🔄 Common Workflows

### Add New Required Field
1. Update IMPORT_ORDERS.md
2. Update `OrdersImport::rules()`
3. Update `OrdersImport::model()`
4. Update `createTemplate()`
5. Update test cases
6. Add migration if needed

### Add New Optional Field
1. Same as above, but mark `nullable` in rules
2. Add default value in model

### Change Batch Size
```php
public function batchSize(): int
{
    return 250; // Changed from 100
}
```

---

## 🎓 Learning Path

1. **Start**: Read `IMPORT_ORDERS.md` specification
2. **Understand**: Read OrderController methods
3. **Study**: Review OrdersImport class
4. **Explore**: Check test cases
5. **Practice**: Create test file and import manually
6. **Debug**: Use tinker to verify data
7. **Extend**: Add new features

---

## 📞 Common Issues & Solutions

### Issue: Template not generating
**Solution**: Check `storage/` directory permissions
```bash
chmod -R 755 storage/
```

### Issue: Import fails silently
**Solution**: Check application logs
```bash
tail -f storage/logs/laravel.log
```

### Issue: Validation error
**Solution**: Verify rules match data format

### Issue: Driver/Vehicle not found
**Solution**: Check database for exact code match

### Issue: Memory exceeded
**Solution**: Split file into smaller chunks

---

## 🚀 Deployment Checklist

- ✅ Composer packages installed
- ✅ Database migrations run
- ✅ Storage directory writable
- ✅ Tests passing
- ✅ Assets built (npm run build)
- ✅ Routes registered
- ✅ No validation errors
- ✅ Documentation reviewed
- ✅ Security validated

---

## 🔗 Related Documentation

- `IMPORT_ORDERS.md` - Feature specification
- `docs/IMPLEMENTATION_IMPORT_EXCEL.md` - Technical details
- `docs/PANDUAN_IMPORT_EXCEL.md` - User guide (Indonesian)
- `IMPLEMENTATION_SUMMARY.md` - Project summary

---

**Last Updated**: 2024
**Version**: 1.0