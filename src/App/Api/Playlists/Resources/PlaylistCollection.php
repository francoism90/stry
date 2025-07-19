<?php

declare(strict_types=1);

namespace App\Api\Playlists\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PlaylistCollection extends ResourceCollection
{
    /**
     * @var string
     */
    public $collects = PlaylistResource::class;

    /**
     * @var bool
     */
    protected $preserveAllQueryParameters = true;
}
