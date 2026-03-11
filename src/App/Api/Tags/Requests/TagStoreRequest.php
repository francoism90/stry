<?php

declare(strict_types=1);

namespace App\Api\Tags\Requests;

use Domain\Tags\Enums\TagType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TagStoreRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(TagType::class)],
            'description' => ['sometimes', 'nullable', 'string', 'max:4096'],
        ];
    }
}
