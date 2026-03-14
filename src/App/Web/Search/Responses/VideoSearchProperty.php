<?php

declare(strict_types=1);

namespace App\Web\Search\Responses;

use App\Api\Videos\Resources\VideoResource;
use Domain\Videos\Models\Video;
use Domain\Videos\Scopes\VideoFilterScope;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;

readonly class VideoSearchProperty implements ProvidesInertiaProperty
{
    public function __construct(protected string $query = '', protected int $limit = 16) {}

    public function toInertiaProperty(PropertyContext $context): ResourceCollection
    {
        return VideoResource::collection(
            Video::search($this->query)
                ->tap(new VideoFilterScope)
                ->take($this->limit)
                ->get()
        );
    }
}
