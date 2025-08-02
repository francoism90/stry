<?php

declare(strict_types=1);

namespace App\Web\Tags\Requests;

use Domain\Tags\Models\Tag;
use Illuminate\Foundation\Http\FormRequest;

class TagIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        logger('auth');

        return $this->user()->can('viewAny', Tag::class);
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'list' => ['sometimes', 'string', 'in:all,watching'],
        ];
    }
}
