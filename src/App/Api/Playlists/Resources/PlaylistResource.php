<?php

declare(strict_types=1);

namespace App\Api\Playlists\Resources;

use Domain\Playlists\Models\Playlist;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Playlist
 */
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
            'encryption_key_id' => $this->encryption_key_id,
            'encryption_key' => $this->encryption_key,
            'asset' => $this->getUrl(),
            'failed' => $this->isFailed(),
            'expired' => $this->isExpired(),
            'valid' => $this->isValid(),
            'type' => $this->type,
            'state' => $this->state->toArray(),
            'expires_at' => $this->expires_at?->toDateTimeString(),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
