<?php

declare(strict_types=1);

namespace Domain\Videos\Scopes;

use Domain\Videos\QueryBuilders\VideoQueryBuilder;
use Laravel\Scout\Builder;

readonly class VideoManageScope
{
    public function __invoke(Builder $scout): void
    {
        // Scout's own query() overwrites rather than chains, so preserve
        // whatever callback a previous tap (e.g. VideoProfileScope) registered.
        $existing = $scout->queryCallback;

        $scout->query(function (VideoQueryBuilder $query) use ($existing): void {
            if ($existing !== null) {
                $existing($query);
            }

            $query->with([
                'media',
                'playlists' => fn ($query) => $query->latest()->limit(10),
                'transcodes' => fn ($query) => $query->latest()->limit(10),
            ]);
        });
    }
}
