<?php

declare(strict_types=1);

namespace Domain\Videos\Algos;

use App\Api\Videos\Resources\VideoResource;
use Domain\Users\Models\User;
use Domain\Videos\Models\Video;
use Illuminate\Http\Resources\Json\ResourceCollection;

class GenerateVideoQueue
{
    public static function make(Video $video, ?User $user = null, ?int $limit = null): ResourceCollection
    {
        return Video::query()
            ->orderByDesc('created_at')
            ->whereKeyNot($video->getKey())
            ->take($limit ?? 12)
            ->get()
            ->toResourceCollection(VideoResource::class);
    }
}
