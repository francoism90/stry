<?php

declare(strict_types=1);

namespace App\Api\Groups\Requests;

use Domain\Groups\Enums\GroupType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GroupStoreRequest extends FormRequest
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
            'type' => ['required', Rule::enum(GroupType::class)],
            'content' => ['sometimes', 'nullable', 'string', 'max:4096'],
        ];
    }
}
