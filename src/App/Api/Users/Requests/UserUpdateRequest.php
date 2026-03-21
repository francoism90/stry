<?php

declare(strict_types=1);

namespace App\Api\Users\Requests;

use Domain\Users\DataObjects\AppearanceSettings;
use Domain\Users\DataObjects\GeneralSettings;
use Domain\Users\DataObjects\PlayerSettings;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserUpdateRequest extends FormRequest
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
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->user),
            ],
            'settings' => ['sometimes', 'nullable', 'array'],
            'settings.general' => ['sometimes', 'nullable', 'array', ...GeneralSettings::rules()],
            'settings.appearance' => ['sometimes', 'nullable', 'array', ...AppearanceSettings::rules()],
            'settings.player' => ['sometimes', 'nullable', 'array', ...PlayerSettings::rules()],
        ];
    }
}
