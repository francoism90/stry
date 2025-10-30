<?php

declare(strict_types=1);

namespace App\Web\Videos\Responses;

use App\Api\Videos\Resources\VideoResource;
use Domain\Users\Models\User;
use Domain\Videos\Models\Video;
use Domain\Videos\QueryBuilders\VideoQueryBuilder;
use Foundation\Container\Attributes\QueryParameter;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Container\Attributes\RouteParameter;
use Illuminate\Pagination\Paginator;
use Inertia\Inertia;
use Inertia\ProvidesInertiaProperties;
use Inertia\RenderContext;
use Laravel\Scout\Builder;

class VideoCollectionProperty implements ProvidesInertiaProperties
{
    public function __construct(
        #[CurrentUser] protected ?User $user = null,
        #[RouteParameter('search')] protected ?string $search = null,
        #[QueryParameter('order')] protected ?string $order = null,
    ) {}

    public function toInertiaProperties(RenderContext $context): array
    {
        return [
            'order' => fn () => $this->order,
            'search' => fn () => $this->search,
            'items' => Inertia::scroll(fn () => VideoResource::collection($this->getBuilder())),
        ];
    }

    protected function getBuilder(): Paginator
    {
        return Video::search($this->search)
            ->query(fn (VideoQueryBuilder $query) => $query->verified()->with('tags'))
            ->simplePaginate(24);
    }
}
