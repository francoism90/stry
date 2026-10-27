<?php

declare(strict_types=1);

namespace Modules\Web\Settings\Requests;

use Domain\Shared\Enums\SnapshotFrequency;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SnapshotSettingsRequest extends FormRequest
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
            'frequency' => ['sometimes', Rule::enum(SnapshotFrequency::class)],
            'keep' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'compress' => ['sometimes', 'boolean'],
        ];
    }
}
