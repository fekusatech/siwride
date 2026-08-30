<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomerTransferBookingService
{
    public function __construct(private CustomerTransferPricingService $pricing) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Order
    {
        return DB::transaction(function () use ($data): Order {
            $data['email'] = Str::lower(trim($data['email']));
            $customer = Customer::query()->firstOrNew(['email' => $data['email']]);
            $customer->name = $data['customer_name'];

            if (! empty($data['customer_phone'])) {
                $customer->phone = $data['customer_phone'];
            }

            $customer->save();

            $estimate = $this->pricing->estimate(
                (float) $data['pickup_latitude'],
                (float) $data['pickup_longitude'],
                (float) $data['dropoff_latitude'],
                (float) $data['dropoff_longitude'],
                (int) $data['passengers'],
            );
            $oneLegPrice = $this->pricing->priceForVehicle(
                $estimate,
                (int) $data['vehicle_category_id'],
            );

            $order = $this->createOrder($data, $customer, $oneLegPrice, $estimate['distance_km']);

            if ($data['trip_type'] === 'round_trip') {
                $returnData = array_merge($data, [
                    'date' => $data['return_date'],
                    'time' => $data['return_time'],
                    'pickup_address' => $data['dropoff_address'],
                    'pickup_latitude' => $data['dropoff_latitude'],
                    'pickup_longitude' => $data['dropoff_longitude'],
                    'dropoff_address' => $data['pickup_address'],
                    'dropoff_latitude' => $data['pickup_latitude'],
                    'dropoff_longitude' => $data['pickup_longitude'],
                ]);

                $returnOrder = $this->createOrder(
                    $returnData,
                    $customer,
                    $oneLegPrice,
                    $estimate['distance_km'],
                    isReturnTrip: true,
                    linkedOrderId: $order->id,
                );
                $order->update(['linked_order_id' => $returnOrder->id]);
            }

            return $order->load(['customer', 'vehicleCategory', 'linkedOrder.vehicleCategory']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function createOrder(
        array $data,
        Customer $customer,
        float $price,
        float $distance,
        bool $isReturnTrip = false,
        ?int $linkedOrderId = null,
    ): Order {
        return Order::create([
            'booking_code' => $this->generateBookingCode(),
            'order_number' => 'ORD'.now()->format('Ymd').strtoupper(Str::random(4)),
            'customer_id' => $customer->id,
            'customer_name' => $data['customer_name'],
            'customer_phone' => $data['customer_phone'] ?? null,
            'customer_email' => $data['email'],
            'date' => $data['date'],
            'time' => $data['time'],
            'pickup_address' => $data['pickup_address'],
            'pickup_latitude' => $data['pickup_latitude'],
            'pickup_longitude' => $data['pickup_longitude'],
            'dropoff_address' => $data['dropoff_address'],
            'dropoff_latitude' => $data['dropoff_latitude'],
            'dropoff_longitude' => $data['dropoff_longitude'],
            'distance_km' => $distance,
            'passengers' => $data['passengers'],
            'notes' => $data['notes'] ?? null,
            'vehicle_category_id' => $data['vehicle_category_id'],
            'price' => $price,
            'parking_gas_fee' => 0,
            'status' => 'pending',
            'is_shared' => true,
            'trip_type' => $data['trip_type'],
            'is_return_trip' => $isReturnTrip,
            'linked_order_id' => $linkedOrderId,
            'payment_status' => 'pending',
        ]);
    }

    private function generateBookingCode(): string
    {
        do {
            $code = 'SW'.strtoupper(Str::random(6));
        } while (Order::query()->where('booking_code', $code)->exists());

        return $code;
    }
}
