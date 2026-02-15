<?php

declare(strict_types=1);

namespace App\Api\Playlists\Requests;

use Domain\Playlists\Enums\PlaylistType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlaylistIndexRequest extends FormRequest
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
            'type' => ['sometimes', 'nullable', Rule::enum(PlaylistType::class)],
        ];
    }
}
