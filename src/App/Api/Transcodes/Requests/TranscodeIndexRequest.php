<?php

declare(strict_types=1);

namespace App\Api\Transcodes\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TranscodeIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
        ];
    }
}
