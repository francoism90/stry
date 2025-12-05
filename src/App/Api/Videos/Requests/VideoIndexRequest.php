<?php

declare(strict_types=1);

namespace App\Api\Videos\Requests;

use Domain\Videos\Enums\VideoList;
use Domain\Videos\Enums\VideoSort;
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
            'search' => ['sometimes', 'nullable', 'string', 'max:255'],
            'sort' => ['sometimes', 'nullable', 'string', Rule::enum(VideoSort::class)],
            'list' => ['sometimes', 'nullable', 'string', Rule::enum(VideoList::class)],
        ];
    }
}
