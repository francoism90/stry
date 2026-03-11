<?php

declare(strict_types=1);

namespace App\Api\Transcodes\Requests;

use Domain\Transcodes\Enums\TranscodeEncoder;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TranscodeIndexRequest extends FormRequest
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
            'encoder' => ['sometimes', 'nullable', Rule::enum(TranscodeEncoder::class)],
        ];
    }
}
