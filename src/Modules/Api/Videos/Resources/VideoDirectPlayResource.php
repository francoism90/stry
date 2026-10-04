<?php

declare(strict_types=1);

namespace Modules\Api\Videos\Resources;

use Domain\Playlists\Models\Playlist;
use Domain\Playlists\States\Verified;
use Domain\Videos\Models\Video;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A direct play stream of a video, shaped like a playlist so the player loads it the same way.
 *
 * @mixin Video
 */
class VideoDirectPlayResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => "direct-{$this->getRouteKey()}",
            'encryption_key_id' => null,
            'encryption_key' => null,
            'asset' => $this->getDirectPlayDashUrl(),
            'asset_dash' => $this->getDirectPlayDashUrl(),
            'asset_hls' => $this->getDirectPlayHlsUrl(),
            'asset_refresh_in' => $this->getDirectPlayRefreshIn(),
            'failed' => false,
            'expired' => false,
            'valid' => true,
            'type' => 'direct',
            'state' => new Verified(new Playlist)->toArray(),
            'expires_at' => null,
            'created_at' => null,
            'updated_at' => null,
        ];
    }
}
