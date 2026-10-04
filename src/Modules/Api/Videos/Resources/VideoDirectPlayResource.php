<?php

declare(strict_types=1);

namespace Modules\Api\Videos\Resources;

use Domain\Playlists\Models\Playlist;
use Domain\Playlists\Settings\PlaylistSettings;
use Domain\Playlists\States\Verified;
use Domain\Videos\Models\Video;
use Foxws\Media\Facades\MediaStream;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Config;

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
        $parameters = ['video' => $this->resource];

        $refreshIn = Config::integer('media.delivery.url_lifetime') - app(PlaylistSettings::class)->manifest_refresh_before;

        return [
            'id' => "direct-{$this->getRouteKey()}",
            'encryption_key_id' => null,
            'encryption_key' => null,
            'asset' => MediaStream::dashUrl('videos', $parameters),
            'asset_dash' => MediaStream::dashUrl('videos', $parameters),
            'asset_hls' => MediaStream::url('videos', $parameters),
            'asset_refresh_in' => max($refreshIn, 0),
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
