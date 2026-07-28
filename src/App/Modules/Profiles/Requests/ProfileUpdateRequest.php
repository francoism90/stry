<?php

declare(strict_types=1);

namespace App\Modules\Profiles\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'avatar' => ['sometimes', 'nullable', 'string', 'max:2048'],
            'is_kids' => ['sometimes', 'boolean'],
            'is_primary' => ['sometimes', 'boolean'],
            'settings' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
