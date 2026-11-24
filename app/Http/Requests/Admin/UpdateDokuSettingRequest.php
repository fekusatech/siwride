<?php

namespace App\Http\Requests\Admin;

use App\Models\DokuSetting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateDokuSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    /** @return array<string, array<mixed>> */
    public function rules(): array
    {
        return [
            'enabled' => ['required', 'boolean'],
            'environment' => ['required', Rule::in(['sandbox', 'production'])],
            'sandbox_client_id' => ['nullable', 'string', 'max:255'],
            'production_client_id' => ['nullable', 'string', 'max:255'],
            'sandbox_secret_key' => ['nullable', 'string', 'max:4096'],
            'production_secret_key' => ['nullable', 'string', 'max:4096'],
        ];
    }

    /** @return array<callable> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if (! $this->boolean('enabled') || ! in_array($this->input('environment'), ['sandbox', 'production'], true)) {
                return;
            }

            $environment = $this->input('environment');
            if (! $this->filled($environment.'_client_id')) {
                $validator->errors()->add($environment.'_client_id', 'Client ID environment aktif wajib diisi.');
            }
            if (! $this->filled($environment.'_secret_key') && ! filled(DokuSetting::query()->find(1)?->getAttribute($environment.'_secret_key'))) {
                $validator->errors()->add($environment.'_secret_key', 'Secret Key environment aktif wajib diisi.');
            }
        }];
    }
}
