<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use App\Api\Videos\Resources\VideoResource;
use Domain\Videos\Models\Video;
use Illuminate\Http\Resources\Json\ResourceCollection;

class GetVideoSuggestions
{
    public function handle(int $limit = 16): ResourceCollection
    {
        return Video::query()
            ->verified()
            ->inRandomOrder()
            ->take($limit)
            ->get()
            ->toResourceCollection(VideoResource::class);
    }
}
