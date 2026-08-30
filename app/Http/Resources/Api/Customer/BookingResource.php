<?php

namespace App\Http\Resources\Api\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'booking_code' => $this->booking_code,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'payment_url' => $this->payment_reference,
            'pickup_address' => $this->pickup_address,
            'dropoff_address' => $this->dropoff_address,
            'date' => $this->date?->format('Y-m-d'),
            'time' => substr((string) $this->time, 0, 5),
            'passengers' => $this->passengers,
            'trip_type' => $this->trip_type,
            'is_return_trip' => $this->is_return_trip,
            'distance_km' => $this->distance_km !== null ? (float) $this->distance_km : null,
            'price' => (float) $this->price,
            'vehicle' => $this->whenLoaded('vehicleCategory', fn (): array => [
                'id' => $this->vehicleCategory?->id,
                'title' => $this->vehicleCategory?->title,
                'image_url' => $this->vehicleCategory?->image_url,
            ]),
            'driver' => $this->whenLoaded('driver', fn (): ?array => $this->driver ? [
                'name' => $this->driver->name,
                'phone' => $this->driver->phone,
            ] : null),
            'return_trip' => $this->whenLoaded('linkedOrder', fn (): ?array => $this->linkedOrder ? [
                'booking_code' => $this->linkedOrder->booking_code,
                'date' => $this->linkedOrder->date?->format('Y-m-d'),
                'time' => substr((string) $this->linkedOrder->time, 0, 5),
                'price' => (float) $this->linkedOrder->price,
            ] : null),
        ];
    }
}
