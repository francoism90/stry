<?php

declare(strict_types=1);

namespace Domain\Playlists\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class PlaylistOrderedScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->ordered();
    }
}
