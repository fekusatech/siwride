<?php

namespace App\Http\Requests\Api\Customer;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'min:3', 'max:100'],
            'email' => ['required', 'email', 'max:100'],
            'customer_phone' => ['nullable', 'string', 'max:20'],
            'pickup_address' => ['required', 'string', 'min:3', 'max:200'],
            'dropoff_address' => ['required', 'string', 'min:3', 'max:200'],
            'pickup_latitude' => ['required', 'numeric', 'between:-90,90'],
            'pickup_longitude' => ['required', 'numeric', 'between:-180,180'],
            'dropoff_latitude' => ['required', 'numeric', 'between:-90,90'],
            'dropoff_longitude' => ['required', 'numeric', 'between:-180,180'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'time' => ['required', 'date_format:H:i'],
            'passengers' => ['required', 'integer', 'min:1', 'max:50'],
            'vehicle_category_id' => ['required', 'integer', 'exists:vehicle_categories,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'trip_type' => ['required', 'string', 'in:one_way,round_trip'],
            'return_date' => ['nullable', 'date', 'after_or_equal:date', 'required_if:trip_type,round_trip'],
            'return_time' => ['nullable', 'date_format:H:i', 'required_if:trip_type,round_trip'],
        ];
    }
}
