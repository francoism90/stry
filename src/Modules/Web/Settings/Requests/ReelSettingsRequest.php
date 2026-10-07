<?php

declare(strict_types=1);

namespace Modules\Web\Settings\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReelSettingsRequest extends FormRequest
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
            'enabled' => ['sometimes', 'boolean'],
            'width' => ['sometimes', 'integer', 'between:240,3840', 'multiple_of:2'],
            'height' => ['sometimes', 'integer', 'between:240,3840', 'multiple_of:2'],
            'fps' => ['sometimes', 'integer', 'between:10,60'],
            'codec' => ['sometimes', 'string', Rule::in(['h264', 'hevc', 'av1'])],
            'hardware' => ['sometimes', 'boolean'],
            'quality' => ['sometimes', 'integer', 'between:15,40'],
            'cuts' => ['sometimes', 'integer', 'between:1,30'],
            'cut_duration' => ['sometimes', 'numeric', 'between:1.5,30'],
            'duration' => ['sometimes', 'integer', 'between:5,180'],
            'scene_threshold' => ['sometimes', 'numeric', 'between:0.05,0.9'],
        ];
    }
}
