<?php

declare(strict_types=1);

namespace Modules\Web\Settings\Requests;

use Domain\Shared\Enums\Language;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class PlaybackSettingsRequest extends FormRequest
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
            'text_language' => ['sometimes', new Enum(Language::class)],
            'encryption' => ['sometimes', 'boolean'],
            'refresh_before' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
