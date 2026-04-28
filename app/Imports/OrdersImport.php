<?php

namespace App\Imports;

use App\Models\Customer;
use App\Models\Driver;
use App\Models\Order;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class OrdersImport implements ToModel, WithBatchInserts, WithHeadingRow, WithValidation
{
    /**
     * Define the validation rules for the imported data.
     */
    public function rules(): array
    {
        return [
            'date' => 'required|date',
            'time' => 'required',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'pickup_address' => 'required|string',
            'dropoff_address' => 'required|string',
            'passengers' => 'nullable|integer|min:1',
            'price' => 'required|numeric|min:0',
            'parking_gas_fee' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:pending,completed,cancelled',
        ];
    }

    /**
     * Transform the model to an Eloquent model.
     *
     * @return Model|null
     */
    public function model(array $row)
    {
        // Parse date - support multiple formats
        $date = $this->parseDate($row['date'] ?? null);

        // Parse time
        $time = $this->parseTime($row['time'] ?? '00:00');

        // Find driver by code or name if provided
        $driverId = null;
        if (isset($row['driver_code']) || isset($row['driver'])) {
            $driverIdentifier = $row['driver_code'] ?? $row['driver'] ?? null;
            if ($driverIdentifier) {
                $driver = Driver::where('nid', $driverIdentifier)
                    ->orWhere('name', 'like', "%{$driverIdentifier}%")
                    ->first();
                $driverId = $driver?->id;
            }
        }

        // Find vehicle by plate or type if provided
        $vehicleId = null;
        if (isset($row['vehicle_plate']) || isset($row['vehicle']) || isset($row['registration_number'])) {
            $vehicleIdentifier = $row['vehicle_plate'] ?? $row['vehicle'] ?? $row['registration_number'] ?? null;
            if ($vehicleIdentifier) {
                $vehicle = Vehicle::where('registration_number', $vehicleIdentifier)
                    ->orWhere('type', 'like', "%{$vehicleIdentifier}%")
                    ->orWhere('model', 'like', "%{$vehicleIdentifier}%")
                    ->first();
                $vehicleId = $vehicle?->id;
            }
        }

        // Generate unique booking code if not provided
        $bookingCode = $row['booking_code'] ?? $this->generateBookingCode();

        // Generate order number if not provided
        $orderNumber = $row['order_number'] ?? $this->generateOrderNumber();

        // Normalize status
        $status = strtolower($row['status'] ?? 'pending');
        if (! in_array($status, ['pending', 'completed', 'cancelled'])) {
            $status = 'pending';
        }

        // Find or create the customer from import data
        $customerName = $row['customer_name'] ?? '';
        $customerPhone = $row['customer_phone'] ?? null;
        $customerEmail = $row['email'] ?? $row['customer_email'] ?? null;

        $customer = null;
        if ($customerEmail) {
            $customer = Customer::firstOrCreate(
                ['email' => $customerEmail],
                ['name' => $customerName, 'phone' => $customerPhone]
            );
        } elseif ($customerName) {
            // No email provided — create by name+phone
            $customer = Customer::firstOrCreate(
                ['name' => $customerName, 'phone' => $customerPhone],
                ['email' => strtolower(str_replace(' ', '.', $customerName)) . '@imported.local']
            );
        }

        return new Order([
            'booking_code' => $bookingCode,
            'order_number' => $orderNumber,
            'customer_id' => $customer?->id,
            'date' => $date,
            'time' => $time,
            'flight_number' => $row['flight_number'] ?? null,
            'driver_id' => $driverId,
            'vehicle_id' => $vehicleId,
            'pickup_address' => $row['pickup_address'] ?? '',
            'pickup_latitude' => $this->parseCoordinate($row['pickup_latitude'] ?? null),
            'pickup_longitude' => $this->parseCoordinate($row['pickup_longitude'] ?? null),
            'dropoff_address' => $row['dropoff_address'] ?? '',
            'dropoff_latitude' => $this->parseCoordinate($row['dropoff_latitude'] ?? null),
            'dropoff_longitude' => $this->parseCoordinate($row['dropoff_longitude'] ?? null),
            'passengers' => $row['passengers'] ?? 1,
            'price' => $this->parseNumber($row['price'] ?? 0),
            'parking_gas_fee' => $this->parseNumber($row['parking_gas_fee'] ?? 0),
            'status' => $status,
        ]);
    }

    /**
     * Parse date from various formats.
     */
    private function parseDate($value): ?Carbon
    {
        if (! $value) {
            return null;
        }

        // If it's already a Carbon instance
        if ($value instanceof Carbon) {
            return $value;
        }

        // If it's a numeric Excel date
        if (is_numeric($value)) {
            return Carbon::createFromTimestampUTC(($value - 25569) * 86400);
        }

        // Try to parse common date formats
        $formats = [
            'Y-m-d',
            'd/m/Y',
            'd-m-Y',
            'm/d/Y',
            'Y/m/d',
            'd F Y',
            'F d, Y',
        ];

        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $value);
            } catch (\Exception $e) {
                continue;
            }
        }

        // Last resort: try Carbon's parse
        try {
            return Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Parse time from various formats.
     */
    private function parseTime($value): string
    {
        if (! $value) {
            return '00:00';
        }

        // If it's a Carbon instance
        if ($value instanceof Carbon) {
            return $value->format('H:i');
        }

        // If it's numeric (Excel time)
        if (is_numeric($value)) {
            $seconds = (int) ($value * 86400);

            return gmdate('H:i', $seconds);
        }

        // Try to parse time string
        try {
            return Carbon::parse($value)->format('H:i');
        } catch (\Exception $e) {
            return '00:00';
        }
    }

    /**
     * Parse coordinate value.
     */
    private function parseCoordinate($value): ?float
    {
        if (! $value || ! is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }

    /**
     * Parse number value (handle comma as decimal separator).
     */
    private function parseNumber($value): float
    {
        if (! $value) {
            return 0;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        // Handle European number format (1.000,00 or 1,000.00)
        $value = str_replace(',', '.', $value);

        return (float) $value;
    }

    /**
     * Generate unique booking code.
     */
    private function generateBookingCode(): string
    {
        $prefix = 'ORD';
        $date = now()->format('Ymd');
        $random = strtoupper(substr(uniqid(), -6));

        return "{$prefix}{$date}{$random}";
    }

    /**
     * Generate order number.
     */
    private function generateOrderNumber(): string
    {
        $prefix = 'SIW';
        $date = now()->format('Ymd');
        $random = strtoupper(substr(uniqid(), -4));

        return "{$prefix}{$date}{$random}";
    }

    /**
     * Get batch size for import.
     */
    public function batchSize(): int
    {
        return 100;
    }
}
