<?php

declare(strict_types=1);

namespace App\Api\Playlists\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PlaylistResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->getRouteKey(),
            'asset' => $this->asset_uri,
            'expires' => $this->expires_at,
            'created' => $this->created_at,
            'updated' => $this->updated_at,
        ];
    }
}
