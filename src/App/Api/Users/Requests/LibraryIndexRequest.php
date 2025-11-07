<?php

declare(strict_types=1);

namespace App\Api\Users\Requests;

use Domain\Users\Enums\LibraryFilter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LibraryIndexRequest extends FormRequest
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
            'filter' => ['sometimes', 'nullable', 'string', Rule::enum(LibraryFilter::class)],
            'view' => ['sometimes', 'nullable', 'string', 'in:vertical,horizontal'],
        ];
    }
}
