<?php

declare(strict_types=1);

namespace App\Api\Videos\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VideoIndexRequest extends FormRequest
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
            'page' => ['sometimes', 'nullable'],
            'search' => ['sometimes', 'nullable', 'string', 'min:1', 'max:255'],
            'sort' => ['sometimes', 'nullable', 'string', 'in:recent,ordered,longest,shortest'],
            'list' => ['sometimes', 'nullable', 'string', 'in:watching,newest'],
        ];
    }
}
