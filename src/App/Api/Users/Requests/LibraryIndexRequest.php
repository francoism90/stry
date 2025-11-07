<?php

declare(strict_types=1);

namespace App\Api\Videos\Requests;

use Domain\Users\Enums\LibraryFilter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LibraryIndexRequest extends FormRequest
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
            'filter' => ['sometimes', 'nullable', 'string', Rule::enum(LibraryFilter::class)],
            'search' => ['sometimes', 'nullable', 'string', 'max:255'],
            'tags' => ['sometimes', 'nullable', 'array', 'max:5'],
            'tags.*.id' => ['required', 'string', 'exists:tags,ulid'],
            'view' => ['sometimes', 'nullable', 'string', 'in:vertical,horizontal'],
        ];
    }
}
