<?php

declare(strict_types=1);

namespace App\Web\Settings\Requests;

use Domain\Shared\Enums\Locale;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class ApplicationSettingsRequest extends FormRequest
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
            'site_name' => ['sometimes', 'string', 'max:255'],
            'timezone' => ['sometimes', 'timezone'],
            'default_locale' => ['sometimes', new Enum(Locale::class)],
            'allow_registration' => ['sometimes', 'boolean'],
            'max_profiles_per_user' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'maintenance_message' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }
}
