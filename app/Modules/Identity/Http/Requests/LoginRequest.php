<?php

namespace App\Modules\Identity\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    use NormalisesPhone;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            ...$this->phoneRules(),
            'password' => ['required', 'string', 'max:128'],
            'code' => ['nullable', 'digits:6'],
            ...$this->deviceRules(),
        ];
    }
}
