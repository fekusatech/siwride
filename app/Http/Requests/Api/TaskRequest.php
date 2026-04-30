<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class TaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'kode_booking' => ['required', 'string', 'max:50', 'unique:mobile_tasks,kode_booking'],
            'no' => ['required', 'integer', 'min:1'],
            'tanggal' => ['required', 'string', 'max:20'],
            'jam' => ['required', 'string', 'max:10'],
            'nama_tamu' => ['required', 'string', 'max:255'],
            'phone_tamu' => ['required', 'string', 'max:50'],
            'flight' => ['nullable', 'string', 'max:50'],
            'pickup' => ['required', 'string'],
            'dropoff' => ['required', 'string'],
            'pickup_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'pickup_lng' => ['nullable', 'numeric', 'between:-180,180'],
            'dropoff_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'dropoff_lng' => ['nullable', 'numeric', 'between:-180,180'],
            'pass' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'integer', 'min:0'],
            'driver_id' => ['nullable', 'exists:mobile_users,id'],
            'driver_name' => ['nullable', 'string', 'max:255'],
            'is_shared' => ['boolean'],
            'is_cash' => ['boolean'],
        ];

        if ($this->isMethod('PUT')) {
            $rules['kode_booking'] = ['required', 'string', 'max:50', 'unique:mobile_tasks,kode_booking,'.$this->route('task')];
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('is_shared')) {
            $this->merge(['is_shared' => filter_var($this->input('is_shared'), FILTER_VALIDATE_BOOLEAN)]);
        }

        if ($this->has('is_cash')) {
            $this->merge(['is_cash' => filter_var($this->input('is_cash'), FILTER_VALIDATE_BOOLEAN)]);
        }
    }
}
