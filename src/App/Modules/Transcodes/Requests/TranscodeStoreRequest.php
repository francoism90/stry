<?php

declare(strict_types=1);

namespace App\Api\Transcodes\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class TranscodeStoreRequest extends FormRequest
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
            'transcodable_type' => ['required', 'string', 'in:video'],
            'transcodable_id' => ['required', 'string', 'exists:videos,ulid'],
        ];
    }
}
