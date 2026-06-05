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
            'pickup_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'pickup_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'dropoff_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'dropoff_longitude' => ['nullable', 'numeric', 'between:-180,180'],
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
            'payment_method' => ['nullable', 'string', 'max:50'],
            'exact_distance_km' => ['nullable', 'numeric'],
        ];
    }

    /**
     * Validate pickup time is in the future when the selected date is today.
     *
     * A 10-minute grace period is applied on top of the standard 30-minute
     * lead time to accommodate time passing while the customer fills out the
     * form. The backend therefore accepts bookings whose pickup time is within
     * the grace window of the strict minimum:
     *
     *   effective minimum = now + 30 min − 10 min grace = now + 20 min
     *
     * Bookings with a pickup time earlier than that effective minimum are still
     * rejected, so genuinely unrealistic times are blocked.
     */
    private function validateTime(): \Closure
    {
        return function ($attribute, $value, $fail) {
            $today = now()->format('Y-m-d');
            $selectedDate = $this->input('date');

            if ($selectedDate === $today) {
                $leadMinutes = 30;
                $gracePeriodMinutes = 10;

                // The strict earliest time the frontend would have shown the customer.
                $strictMin = now()->addMinutes($leadMinutes);
                $strictMinRemainder = (int) $strictMin->format('i') % 5;
                if ($strictMinRemainder !== 0) {
                    $strictMin->addMinutes(5 - $strictMinRemainder);
                }

                // The effective minimum after subtracting the grace period.
                $effectiveMin = $strictMin->copy()->subMinutes($gracePeriodMinutes);

                // Round down to the nearest 5-minute slot so we don't inadvertently
                // tighten the window due to rounding.
                $effectiveMinRemainder = (int) $effectiveMin->format('i') % 5;
                if ($effectiveMinRemainder !== 0) {
                    $effectiveMin->subMinutes($effectiveMinRemainder);
                }

                $effectiveMinStr = $effectiveMin->format('H:i');
                $strictMinFriendly = $strictMin->format('h:i A');

                if ($value < $effectiveMinStr) {
                    $fail("Pickup time must be at least 30 minutes from now. Earliest pickup for today is {$strictMinFriendly}.");
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
