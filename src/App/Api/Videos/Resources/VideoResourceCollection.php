<?php

declare(strict_types=1);

namespace App\Api\Videos\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;

class VideoResourceCollection extends AnonymousResourceCollection
{
    public function toArray($request): array
    {
        $this->preloadGroupTypes($request);

        return parent::toArray($request);
    }

    /**
     * Resolve the viewer's group memberships for every video at once to avoid per-item queries.
     */
    protected function preloadGroupTypes(Request $request): void
    {
        $user = $request->user();

        if (! $user || $this->collection->isEmpty()) {
            return;
        }

        $groupTypes = $user->groupTypesFor(
            $this->collection->map(fn (VideoResource $resource) => $resource->resource),
        );

        $this->collection->each(fn (VideoResource $resource) => $resource->withGroupTypes(
            $groupTypes->get($resource->resource->getKey(), Collection::make()),
        ));
    }
}
