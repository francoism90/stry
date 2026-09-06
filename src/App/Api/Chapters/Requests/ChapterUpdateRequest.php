<?php

declare(strict_types=1);

namespace App\Api\Chapters\Requests;

use Domain\Chapters\Enums\ChapterType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChapterUpdateRequest extends FormRequest
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
            'label' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', 'nullable', Rule::enum(ChapterType::class)],
            'start_time' => ['sometimes', 'numeric', 'min:0'],
            'end_time' => ['sometimes', 'numeric', 'min:0'],
            'sort' => ['sometimes', 'nullable', 'integer', 'min:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $chapter = $this->route('chapter');

            $startTime = $this->input('start_time', $chapter?->start_time);
            $endTime = $this->input('end_time', $chapter?->end_time);

            if ($startTime !== null && $endTime !== null && (float) $startTime >= (float) $endTime) {
                $validator->errors()->add('end_time', __('The end time must be after the start time.'));
            }
        });
    }
}
