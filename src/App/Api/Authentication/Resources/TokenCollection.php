<?php

declare(strict_types=1);

namespace App\Api\Authentication\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class TokenCollection extends ResourceCollection
{
    /**
     * @var string
     */
    public $collects = TokenResource::class;
}
