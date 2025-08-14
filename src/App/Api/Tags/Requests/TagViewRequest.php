<?php

declare(strict_types=1);

namespace App\Api\Tags\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TagViewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
}
