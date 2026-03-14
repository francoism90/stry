<?php

declare(strict_types=1);

namespace App\Api\Groups\Requests;

use Domain\Groups\Enums\GroupOrder;
use Domain\Groups\Enums\GroupType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GroupIndexRequest extends FormRequest
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
            'type' => ['sometimes', 'nullable', Rule::enum(GroupType::class)],
            'order' => ['sometimes', 'nullable', Rule::enum(GroupOrder::class)],
        ];
    }
}
