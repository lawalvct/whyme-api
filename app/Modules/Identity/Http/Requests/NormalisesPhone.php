<?php

namespace App\Modules\Identity\Http\Requests;

use App\Modules\Identity\Enums\DevicePlatform;
use App\Modules\Identity\Services\PhoneNumber;
use Illuminate\Validation\Rule;

/**
 * Shared by the auth requests: phone becomes E.164 before validation, and the
 * device block identifies the phone, tablet or browser signing in.
 */
trait NormalisesPhone
{
    protected function prepareForValidation(): void
    {
        if ($this->has('phone')) {
            $this->merge(['phone' => PhoneNumber::normalise((string) $this->input('phone')) ?? $this->input('phone')]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function phoneRules(): array
    {
        return ['phone' => ['required', 'string', 'regex:/^\+234\d{10}$/']];
    }

    /**
     * @return array<string, mixed>
     */
    protected function deviceRules(): array
    {
        return [
            'device' => ['required', 'array'],
            'device.id' => ['required', 'string', 'min:8', 'max:64'],
            'device.platform' => ['required', Rule::enum(DevicePlatform::class)],
            'device.name' => ['nullable', 'string', 'max:100'],
            'device.app_version' => ['nullable', 'string', 'max:20'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return ['phone.regex' => __('errors.phone_invalid')];
    }
}
