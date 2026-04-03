<?php

declare(strict_types=1);

namespace App\Api\Profiles\Requests;

use Domain\Profiles\Enums\ProfileOrder;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileIndexRequest extends FormRequest
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
            'order' => ['sometimes', 'nullable', Rule::enum(ProfileOrder::class)],
        ];
    }
}
