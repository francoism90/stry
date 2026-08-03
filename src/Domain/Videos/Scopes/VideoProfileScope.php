<?php

declare(strict_types=1);

namespace Domain\Videos\Scopes;

use Domain\Profiles\Models\Profile;
use Domain\Videos\QueryBuilders\VideoQueryBuilder;
use Laravel\Scout\Builder;

readonly class VideoProfileScope
{
    public function __invoke(Builder $scout): void
    {
<<<<<<< HEAD
        $scout
            ->query(fn (VideoQueryBuilder $query) => $query
                ->with(['media', 'tags'])
                ->forProfile(Profile::current()));
=======
        $scout->query(fn (VideoQueryBuilder $query) => $query
            ->with(['media', 'tags'])
            ->forProfile(Profile::current()));
>>>>>>> 402807bf (fix: format query method in VideoProfileScope for improved readability)
    }
}
