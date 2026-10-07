<?php

declare(strict_types=1);

namespace Modules\Web\Settings\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProcessingSettingsRequest extends FormRequest
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
            'extract_captions' => ['sometimes', 'boolean'],
            'extract_chapters' => ['sometimes', 'boolean'],
            'extract_storyboard' => ['sometimes', 'boolean'],
            'create_renditions' => ['sometimes', 'boolean'],
            'create_reels' => ['sometimes', 'boolean'],
            'reel_width' => ['sometimes', 'integer', 'between:240,3840', 'multiple_of:2'],
            'reel_height' => ['sometimes', 'integer', 'between:240,3840', 'multiple_of:2'],
            'reel_fps' => ['sometimes', 'integer', 'between:10,60'],
        ];
    }
}
