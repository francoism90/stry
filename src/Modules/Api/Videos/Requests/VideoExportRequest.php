<?php

declare(strict_types=1);

namespace Modules\Api\Videos\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class VideoExportRequest extends FormRequest
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
            'delete' => ['sometimes', 'boolean'],
        ];
    }

    public function shouldDeleteAfterExport(): bool
    {
        return $this->boolean('delete');
    }
}
