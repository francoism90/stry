<?php

declare(strict_types=1);

namespace Domain\Videos\Algos;

use App\Api\Videos\Resources\VideoResource;
use Domain\Videos\Models\Video;
use Illuminate\Http\Resources\Json\ResourceCollection;

class GenerateVideoRecommendation
{
    public static function make(?string $type = null, ?int $limit = null): ResourceCollection
    {
        return Video::query()
            ->orderByDesc('created_at')
            ->take($limit ?? 12)
            ->get()
            ->toResourceCollection(VideoResource::class);
    }
}
