<?php

declare(strict_types=1);

namespace App\Api\Playlists\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlaylistUpdateRequest extends FormRequest
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
            'type' => ['sometimes', 'nullable', 'string', 'max:255'],
            'expires_at' => ['sometimes', 'nullable', 'date'],
        ];
    }
}
