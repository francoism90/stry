<?php

declare(strict_types=1);

namespace App\Api\Tags\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TagViewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('view', $this->tag);
    }
}
