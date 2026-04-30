<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'vehicle_type' => ['required', 'string', Rule::in(['economy', 'premium', 'van', 'bus', 'special'])],
            'notes' => ['nullable', 'string', 'max:1000'],
            'create_account' => ['nullable', 'boolean'],
            'password' => ['nullable', 'string', 'min:8', 'required_if_accepted:create_account'],
            'payment_method' => ['nullable', 'string', 'in:cash,transfer'],
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

            // If date is today, check time is in the future
            if ($selectedDate === $today) {
                $currentTime = now()->format('H:i');
                if ($value <= $currentTime) {
                    $fail('Waktu pickup harus di masa depan. Silakan pilih waktu setelah '.$currentTime.'.');
                }
            }
        };
    }

    public function messages(): array
    {
        return [
            'customer_name.required' => 'Nama lengkap wajib diisi.',
            'customer_name.min' => 'Nama minimal 3 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'pickup_address.required' => 'Lokasi pickup wajib diisi.',
            'dropoff_address.required' => 'Lokasi tujuan wajib diisi.',
            'date.required' => 'Tanggal pickup wajib diisi.',
            'date.after_or_equal' => 'Tanggal pickup minimal hari ini.',
            'time.required' => 'Waktu pickup wajib diisi.',
            'passengers.required' => 'Jumlah penumpang wajib diisi.',
            'vehicle_type.required' => 'Tipe kendaraan wajib dipilih.',
            'vehicle_type.in' => 'Tipe kendaraan tidak valid.',
            'password.required_if_accepted' => 'Password wajib diisi jika Anda memilih untuk membuat akun.',
            'password.min' => 'Password minimal 8 karakter.',
        ];
    }
}
