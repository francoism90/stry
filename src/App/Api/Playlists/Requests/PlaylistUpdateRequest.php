<?php

declare(strict_types=1);

namespace App\Api\Playlists\Requests;

use Domain\Playlists\Enums\PlaylistType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'type' => ['sometimes', Rule::enum(PlaylistType::class)],
            'expires_at' => ['sometimes', 'nullable', 'date_format:Y-m-d H:i:s'],
        ];
    }
}
