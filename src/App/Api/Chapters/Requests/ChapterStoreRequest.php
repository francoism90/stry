<?php

declare(strict_types=1);

namespace App\Api\Chapters\Requests;

use Domain\Chapters\Enums\ChapterType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChapterStoreRequest extends FormRequest
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
            'label' => ['required', 'string', 'max:255'],
            'type' => ['nullable', Rule::enum(ChapterType::class)],
            'start_time' => ['required', 'numeric', 'min:0', 'lt:end_time'],
            'end_time' => ['required', 'numeric', 'gt:start_time'],
            'sort' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
