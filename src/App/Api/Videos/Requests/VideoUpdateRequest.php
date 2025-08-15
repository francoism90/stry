<?php

declare(strict_types=1);

namespace App\Api\Videos\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VideoUpdateRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'episode' => ['nullable', 'string', 'max:255'],
            'season' => ['nullable', 'string', 'max:255'],
            'part' => ['nullable', 'string', 'max:255'],
            'snapshot' => ['nullable', 'numeric'],
            'tags' => ['nullable', 'array', 'max:10'],
            'tags.*.id' => ['required', 'string', 'exists:tags,ulid'],
            'summary' => ['nullable', 'string', 'max:2048'],
            'expires_at' => ['nullable', 'date_format:Y-m-d H:i:s'],
            'published_at' => ['nullable', 'date_format:Y-m-d H:i:s'],
            'released_at' => ['nullable', 'date_format:Y-m-d H:i:s'],
        ];
    }
}
