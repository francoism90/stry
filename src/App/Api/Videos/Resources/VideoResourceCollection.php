<?php

declare(strict_types=1);

namespace App\Api\Videos\Resources;

use Domain\Videos\Collections\VideoCollection;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class VideoResourceCollection extends AnonymousResourceCollection
{
    public function toArray($request): array
    {
        if ($user = $request->user()) {
            VideoCollection::make($this->collection->pluck('resource'))->loadGroupTypesFor($user);
        }

        return parent::toArray($request);
    }
}
