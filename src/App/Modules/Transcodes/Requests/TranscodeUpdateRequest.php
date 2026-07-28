<?php

declare(strict_types=1);

namespace App\Modules\Transcodes\Requests;

use Domain\Transcodes\Enums\TranscodeEncoder;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TranscodeUpdateRequest extends FormRequest
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
            'encoder' => ['sometimes', Rule::enum(TranscodeEncoder::class)],
        ];
    }
}
