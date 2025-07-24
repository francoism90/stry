<?php

declare(strict_types=1);

namespace Domain\Videos\Algos;

use App\Api\Videos\Resources\VideoResource;
use Domain\Users\Models\User;
use Domain\Videos\Models\Video;
use Illuminate\Http\Resources\Json\ResourceCollection;

class GenerateVideoCollection
{
    public static function make(?User $user = null, ?string $type = null, ?int $limit = null): ResourceCollection
    {
        return Video::query()
            ->orderByDesc('created_at')
            ->take($limit ?? 12)
            ->get()
            ->toResourceCollection(VideoResource::class);
    }
}
