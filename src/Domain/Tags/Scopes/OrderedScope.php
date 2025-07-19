<?php

declare(strict_types=1);

namespace Domain\Tags\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class OrderedScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->orderBy('order_column');
    }
}
