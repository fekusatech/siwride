<?php

namespace App\Http\Resources\Api\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'description' => $this->description,
            'examples' => $this->examples,
            'passenger_capacity' => $this->passenger_capacity,
            'luggage_capacity' => $this->luggage_capacity,
            'base_price' => (float) $this->base_price,
            'price_per_km' => (float) $this->price_per_km,
            'image_url' => $this->image_url,
        ];
    }
}
