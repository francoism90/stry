<?php

declare(strict_types=1);

namespace Domain\Videos\Scopes;

use App\Api\Videos\Requests\VideoIndexRequest;
use Laravel\Scout\Builder;

readonly class VideoFilterScope
{
    public function __construct(
        public VideoIndexRequest $request,
    ) {}

    public function __invoke(Builder $scout): void
    {
        $scout;
    }
}
