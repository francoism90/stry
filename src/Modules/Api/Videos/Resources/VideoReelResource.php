<?php

declare(strict_types=1);

namespace Modules\Api\Videos\Resources;

use Domain\Groups\Enums\GroupType;
use Domain\Videos\Models\Video;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Video
 */
class VideoReelResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $user = $request->user();

        return [
            'id' => $this->getRouteKey(),
            'title' => $this->title,
            'thumb' => $this->thumb,
            'reel_url' => $this->getReelUrl(),
            'liked' => $user ? $this->isInGroupOf($user, GroupType::Liked) : null,
            'saved' => $user ? $this->isInGroupOf($user, GroupType::Saved) : null,
        ];
    }
}
