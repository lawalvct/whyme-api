<?php

namespace App\Modules\Identity\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    use NormalisesPhone;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:120'],
            ...$this->phoneRules(),
            'password' => ['required', 'string', 'min:8', 'max:128'],
            'code' => ['required', 'digits:6'],
            ...$this->deviceRules(),
        ];
    }
}
