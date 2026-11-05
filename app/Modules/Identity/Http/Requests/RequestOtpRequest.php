<?php

namespace App\Modules\Identity\Http\Requests;

use App\Modules\Identity\Enums\OtpPurpose;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RequestOtpRequest extends FormRequest
{
    use NormalisesPhone;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            ...$this->phoneRules(),
            'purpose' => ['required', Rule::enum(OtpPurpose::class)],
        ];
    }
}
