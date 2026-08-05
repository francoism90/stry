<?php

declare(strict_types=1);

<<<<<<<< HEAD:src/App/Modules/Playlists/Requests/PlaylistViewRequest.php
namespace App\Modules\Playlists\Requests;
========
namespace App\Api\Videos\Requests;
>>>>>>>> c9d0945b (refactor: use Shaka Manager (#497)):src/App/Modules/Videos/Requests/VideoViewRequest.php

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
