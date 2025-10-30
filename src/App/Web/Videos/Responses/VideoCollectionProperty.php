<?php

declare(strict_types=1);

namespace App\Web\Videos\Responses;

use App\Api\Videos\Resources\VideoResource;
use Domain\Videos\Models\Video;
use Foundation\Container\Attributes\QueryParameter;
use Illuminate\Container\Attributes\RouteParameter;
use Inertia\ProvidesInertiaProperties;
use Inertia\RenderContext;
use Inertia\Inertia;

class VideoCollectionProperty implements ProvidesInertiaProperties
{
    public function __construct(
        #[RouteParameter('search')] protected ?string $search = null,
        #[QueryParameter('order')] protected ?string $order = null,
    ) {}

    public function toInertiaProperties(RenderContext $context): array
    {
        return [
            'order' => fn () => $this->order,
            'search' => fn () => $this->search,
            // 'items' => Inertia::scroll(fn () => VideoResource::collection(Video::search('')
            //     ->simplePaginate(24)
            // )),
        ];
    }
}
