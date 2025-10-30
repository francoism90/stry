<?php

declare(strict_types=1);

namespace App\Api\Videos\Requests;

use Domain\Videos\Enums\VideoOrder;
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
            'filter' => ['sometimes', 'nullable', 'string', Rule::enum(VideoOrder::class)],
            'order' => ['sometimes', 'nullable', 'string', 'min:1', 'max:255'],
            'tags' => ['sometimes', 'nullable', 'array', 'max:5'],
            'tags.*.id' => ['required', 'string', 'exists:tags,ulid'],
        ];
    }
}
