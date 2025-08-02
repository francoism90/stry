<?php

declare(strict_types=1);

namespace App\Web\Videos\Requests;

use Domain\Videos\Models\Video;
use Illuminate\Foundation\Http\FormRequest;

class VideoIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', Video::class);
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'list' => ['required', 'string', 'in:all,watching'],
        ];
    }
}
