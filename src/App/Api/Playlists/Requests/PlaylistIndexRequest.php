<?php

declare(strict_types=1);

namespace App\Api\Playlists\Requests;

use Domain\Playlist\Enums\PlaylistSort;
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
            'sort' => ['sometimes', 'nullable', 'string', Rule::enum(PlaylistSort::class)],
            'search' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
