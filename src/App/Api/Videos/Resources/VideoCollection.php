<?php

declare(strict_types=1);

namespace App\Api\Videos\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class VideoCollection extends ResourceCollection
{
    /**
     * @var string
     */
    public $collects = VideoResource::class;

    /**
     * @var bool
     */
    protected $preserveAllQueryParameters = true;
}
