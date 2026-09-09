<?php

declare(strict_types=1);

namespace App\Web\Settings\Requests;

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
        ];
    }
}
