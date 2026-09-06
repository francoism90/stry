<?php

declare(strict_types=1);

namespace App\Web\Settings\Requests;

use Domain\Chapters\Enums\ChapterType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class ChapterSettingsRequest extends FormRequest
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
            'patterns' => ['sometimes', 'string', 'json'],
            'default_type' => ['sometimes', new Enum(ChapterType::class)],
        ];
    }
}
