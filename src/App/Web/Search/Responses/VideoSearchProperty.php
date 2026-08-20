<?php

declare(strict_types=1);

namespace App\Web\Search\Responses;

use App\Api\Videos\Resources\VideoResource;
use Domain\Videos\Models\Video;
use Domain\Videos\Scopes\VideoProfileScope;
use Foxws\ScoutBuilder\AllowedSort;
use Foxws\ScoutBuilder\ScoutBuilder;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;
use Support\Scout\Sorts\RecommendedSorter;

readonly class VideoSearchProperty implements ProvidesInertiaProperty
{
    public function __construct(protected string $query = '', protected int $limit = 16) {}

    public function toInertiaProperty(PropertyContext $context): ResourceCollection
    {
        // Relevant sort options
        $recommendedSort = AllowedSort::custom('recommended', new RecommendedSorter);

        return VideoResource::collection(
            ScoutBuilder::for(Video::search($this->query))
                ->tap(new VideoProfileScope)
                ->allowedSorts(
                    $recommendedSort,
                )
                ->defaultSort($recommendedSort)
                ->take($this->limit)
                ->get()
        );
    }
}
