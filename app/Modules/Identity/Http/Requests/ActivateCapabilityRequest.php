<?php

namespace App\Modules\Identity\Http\Requests;

use App\Modules\Identity\Enums\Capability;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ActivateCapabilityRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'capability' => ['required', Rule::enum(Capability::class)],
            'terms_version_id' => ['nullable', 'integer'],
        ];
    }
}
