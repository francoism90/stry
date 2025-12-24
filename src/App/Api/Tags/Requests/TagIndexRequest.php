<?php

declare(strict_types=1);

namespace App\Api\Tags\Requests;

use Domain\Tags\Enums\TagType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TagIndexRequest extends FormRequest
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
            'type' => ['sometimes', 'nullable', Rule::enum(TagType::class)],
            'search' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
