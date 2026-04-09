<?php

declare(strict_types=1);

namespace App\Api\Profiles\Requests;

use Domain\Profiles\Enums\ProfileSorter;
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
            'sort' => ['sometimes', 'nullable', Rule::enum(ProfileSorter::class)],
        ];
    }
}
