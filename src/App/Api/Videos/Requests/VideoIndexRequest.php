<?php

declare(strict_types=1);

namespace App\Api\Videos\Requests;

use Domain\Videos\Enums\VideoSorter;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VideoIndexRequest extends FormRequest
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
            'search' => ['sometimes', 'nullable', 'string', 'max:255'],
            'sort' => ['sometimes', 'nullable', Rule::enum(VideoSorter::class)],
            'direction' => ['sometimes', 'nullable', Rule::in(['asc', 'desc'])],
        ];
    }
}
