<?php

declare(strict_types=1);

namespace App\Api\Tags\Requests;

use Domain\Tags\Enums\TagType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TagUpdateRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(TagType::class)],
            'related' => ['sometimes', 'nullable', 'array', 'max:10'],
            'related.*.id' => ['required', 'string', 'exists:tags,ulid'],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }
}
