<?php

declare(strict_types=1);

namespace App\Api\Videos\Requests;

use Domain\Videos\Enums\VideoType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VideoIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'search' => ['sometimes', 'nullable', 'string', 'min:1', 'max:255'],
            'type' => ['sometimes', 'nullable', 'string', Rule::enum(VideoType::class)],
            'tags' => ['sometimes', 'nullable', 'array', 'max:5'],
            'tags.*.id' => ['required', 'string', 'exists:tags,ulid'],
        ];
    }
}
