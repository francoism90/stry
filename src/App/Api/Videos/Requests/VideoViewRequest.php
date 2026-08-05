<?php

declare(strict_types=1);

namespace App\Api\Videos\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class VideoViewRequest extends FormRequest
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
            'time' => ['sometimes', 'nullable', 'numeric', 'min:0'],
        ];
    }
}
