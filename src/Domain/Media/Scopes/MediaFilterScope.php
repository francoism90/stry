<?php

declare(strict_types=1);

namespace Domain\Media\Scopes;

use Illuminate\Database\Eloquent\Builder;

readonly class MediaFilterScope
{
    public function __construct(
        // public MediaType|string|null $type = null,
    ) {}

    public function __invoke(Builder $query): void
    {
        $query->latest();
    }
}
