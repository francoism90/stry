<?php

declare(strict_types=1);

namespace App\Web\Videos\Responses;

use App\Api\Videos\Resources\VideoResource;
use Domain\Videos\Models\Video;
use Domain\Videos\QueryBuilders\VideoQueryBuilder;
use Domain\Videos\Scopes\VideoFilterScope;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;

readonly class VideoSectionCollection implements ProvidesInertiaProperty
{
    public function __construct(
        protected readonly ?string $type = null,
        protected readonly ?int $limit = 16,
    ) {}

    public function toInertiaProperty(PropertyContext $context): mixed
    {
        return Video::query()
            ->with('tags')
            ->tap(new VideoFilterScope(type: $this->type))
            ->when($this->limit, fn (VideoQueryBuilder $query) => $query->limit($this->limit))
            ->get()
            ->toResourceCollection(VideoResource::class);
    }
}
