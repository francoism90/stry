<?php

declare(strict_types=1);

namespace Domain\Relates\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ScoreScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder
            ->orderByDesc('score')
            ->orderByDesc('boost')
            ->oldest();
    }
}
