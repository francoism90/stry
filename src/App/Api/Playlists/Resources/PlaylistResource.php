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
            'percent' => $this->getPercentage(),
            'state' => $this->state->label(),
            'expires_at' => $this->expires_at,
            $this->mergeWhen($request->user()->isAdmin(), [
                'type' => $this->type,
                'accessed_at' => $this->accessed_at,
                'transcoded_at' => $this->transcoded_at,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ]),
        ];
    }
}
