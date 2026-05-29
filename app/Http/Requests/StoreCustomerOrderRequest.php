<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'min:3', 'max:100'],
            'email' => ['required', 'email', 'max:100'],
            'customer_phone' => ['nullable', 'string', 'max:20'],
            'pickup_address' => ['required', 'string', 'min:3', 'max:200'],
            'dropoff_address' => ['required', 'string', 'min:3', 'max:200'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'time' => ['required', 'string', $this->validateTime()],
            'passengers' => ['required', 'integer', 'min:1', 'max:50'],
            'vehicle_type' => ['nullable', 'string', 'max:50'],
            'vehicle_category_id' => ['nullable', 'integer', 'exists:vehicle_categories,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'extras' => ['nullable', 'array'],
            'extras.*.label' => ['required_with:extras', 'string', 'max:100'],
            'extras.*.price' => ['required_with:extras', 'numeric', 'min:0'],
            'create_account' => ['nullable', 'boolean'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed', 'required_if_accepted:create_account'],
            'payment_method' => ['nullable', 'string', 'in:cash,transfer,visa,mastercard,paypal,apple_pay,google_pay'],
        ];
    }

    /**
     * Validate time is in the future when date is today
     */
    private function validateTime(): \Closure
    {
        return function ($attribute, $value, $fail) {
            $today = now()->format('Y-m-d');
            $selectedDate = $this->input('date');

            if ($selectedDate === $today) {
                $currentTime = now()->format('H:i');
                if ($value <= $currentTime) {
                    $fail('Pickup time must be in the future. Please select a time after '.$currentTime.'.');
                }
            }
        };
    }

    public function messages(): array
    {
        return [
            'customer_name.required' => 'Full name is required.',
            'customer_name.min' => 'Name must be at least 3 characters.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'pickup_address.required' => 'Pickup location is required.',
            'dropoff_address.required' => 'Destination is required.',
            'date.required' => 'Pickup date is required.',
            'date.after_or_equal' => 'Pickup date must be today or in the future.',
            'time.required' => 'Pickup time is required.',
            'passengers.required' => 'Number of passengers is required.',
            'password.required_if_accepted' => 'Password is required when creating an account.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Passwords do not match.',
        ];
    }
}
