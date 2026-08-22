<?php

declare(strict_types=1);

namespace App\Api\Videos\Requests;

use Domain\Videos\Enums\VideoLibraryScope;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VideoUpdateRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'episode' => ['nullable', 'string', 'max:255'],
            'season' => ['nullable', 'string', 'max:255'],
            'part' => ['nullable', 'string', 'max:255'],
            'adult' => ['sometimes', 'boolean'],
            'snapshot' => ['nullable', 'numeric', 'min:0'],
            'tags' => ['nullable', 'array', 'max:15'],
            'tags.*.id' => ['required', 'string', 'exists:tags,ulid'],
            'titles' => ['sometimes', 'nullable', 'string', 'max:1024'],
            'summary' => ['sometimes', 'nullable', 'string', 'max:4096'],
            'expires_at' => ['sometimes', 'nullable', 'date_format:Y-m-d H:i:s'],
            'published_at' => ['sometimes', 'nullable', 'date_format:Y-m-d H:i:s'],
            'released_at' => ['sometimes', 'nullable', 'date_format:Y-m-d'],
            'state' => ['sometimes', Rule::enum(VideoLibraryScope::class)->except(VideoLibraryScope::All)],
        ];
    }
}
