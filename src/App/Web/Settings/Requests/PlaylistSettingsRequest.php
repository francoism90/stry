<?php

declare(strict_types=1);

namespace App\Web\Settings\Requests;

use Domain\Playlists\Enums\EncryptionMethod;
use Domain\Playlists\Enums\PlaylistType;
use Domain\Playlists\Enums\ProtectionScheme;
use Domain\Shared\Enums\Language;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class PlaylistSettingsRequest extends FormRequest
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
            'type' => ['sometimes', new Enum(PlaylistType::class)],
            'disk_name' => ['sometimes', 'string', 'max:255'],
            'language' => ['sometimes', new Enum(Language::class)],
            'text_language' => ['sometimes', new Enum(Language::class)],
            'expires_after' => ['sometimes', 'integer', 'min:0'],
            'manifest_cache_lifetime' => ['sometimes', 'integer', 'min:0'],
            'manifest_url_lifetime' => ['sometimes', 'integer', 'min:1'],
            'manifest_refresh_before' => ['sometimes', 'integer', 'min:0'],
            'media_url_lifetime' => ['sometimes', 'integer', 'min:1'],
            'key_url_lifetime' => ['sometimes', 'integer', 'min:1'],
            'encryption' => ['sometimes', 'nullable', new Enum(EncryptionMethod::class)],
            'protection_scheme' => ['sometimes', 'nullable', new Enum(ProtectionScheme::class)],
            'key_rotation' => ['sometimes', 'boolean'],
            'key_rotation_duration' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
