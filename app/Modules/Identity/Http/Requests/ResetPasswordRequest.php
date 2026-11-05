<?php

namespace App\Modules\Identity\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    use NormalisesPhone;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            ...$this->phoneRules(),
            'code' => ['required', 'digits:6'],
            'password' => ['required', 'string', 'min:8', 'max:128'],
        ];
    }
}
