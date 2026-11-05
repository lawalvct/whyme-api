<?php

namespace App\Modules\Identity\Http\Requests;

use App\Modules\Platform\Services\Languages;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'version' => ['required', 'integer', 'min:1'],
            'name' => ['sometimes', 'string', 'min:2', 'max:120'],
            'locale' => ['sometimes', 'string', Rule::in(app(Languages::class)->enabledCodes())],
        ];
    }
}
