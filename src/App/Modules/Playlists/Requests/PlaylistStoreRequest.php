<?php

declare(strict_types=1);

namespace App\Modules\Playlists\Requests;

use Domain\Playlists\Enums\PlaylistType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlaylistStoreRequest extends FormRequest
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
            'type' => ['required', Rule::enum(PlaylistType::class)],
        ];
    }
}
