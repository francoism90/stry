<?php

declare(strict_types=1);

namespace Modules\Api\Videos\Resources;

use Domain\Videos\Models\Video;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The signed manifest URLs of a video's direct play stream, and when the player should renew them.
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
            'asset' => $this->getDirectPlayDashUrl(),
            'asset_dash' => $this->getDirectPlayDashUrl(),
            'asset_hls' => $this->getDirectPlayHlsUrl(),
            'asset_refresh_in' => $this->getDirectPlayRefreshIn(),
        ];
    }
}
