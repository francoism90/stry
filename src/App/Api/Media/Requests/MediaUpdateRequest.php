<?php

declare(strict_types=1);

namespace App\Api\Media\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MediaUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'custom_properties' => ['sometimes', 'nullable', 'string', 'json'],
            'order_column' => ['sometimes', 'nullable', 'integer', 'min:0'],
        ];
    }
}
