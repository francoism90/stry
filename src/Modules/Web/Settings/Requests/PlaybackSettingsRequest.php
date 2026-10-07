<?php

declare(strict_types=1);

namespace Modules\Web\Settings\Requests;

use Domain\Shared\Enums\Language;
use Foxws\Media\Encoding\Ladder;
use Foxws\Media\Encoding\Rendition;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
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
            'completion_threshold' => ['sometimes', 'numeric', 'between:0.5,1'],
            'renditions' => ['sometimes', 'array'],
            'renditions.*' => ['integer', 'distinct', Rule::in(array_map(fn (Rendition $rendition): int => $rendition->height, Ladder::standard()->renditions))],
        ];
    }
}
