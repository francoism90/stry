<?php

declare(strict_types=1);

namespace App\Api\Playlists\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PlaylistResource extends JsonResource
{
    /**
     * @var bool
     */
    public $preserveKeys = true;

    public function toArray($request): array
    {
        return [
            'id' => $this->getRouteKey(),
            'asset' => $this->getUrl(),
            'valid' => $this->isValid(),
            'type' => $this->type,
            'state' => $this->state->label(),
            'expires_at' => $this->expires_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
