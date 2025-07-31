<?php

declare(strict_types=1);

namespace Domain\Tags\Algos;

use App\Api\Tags\Resources\TagResource;
use Domain\Tags\Models\Tag;
use Illuminate\Http\Resources\Json\ResourceCollection;

class GetMatchingTagCollection
{
    public static function make(?string $term = null, ?int $limit = null): ResourceCollection
    {
        return Tag::search($term ?? '*')
            ->take($limit ?? 12)
            ->get()
            ->toResourceCollection(TagResource::class);
    }
}
