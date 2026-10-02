<?php

declare(strict_types=1);

namespace Modules\Api\Videos\Resources;

use Domain\Videos\Collections\VideoCollection;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use JsonSerializable;

class VideoResourceCollection extends AnonymousResourceCollection
{
    /**
     * @return array<array-key, mixed>|Arrayable<array-key, mixed>|JsonSerializable
     */
    public function toArray($request): array|Arrayable|JsonSerializable
    {
        if ($user = $request->user()) {
            VideoCollection::make($this->collection?->pluck('resource'))->loadGroupTypesFor($user);
        }

        return parent::toArray($request);
    }
}
