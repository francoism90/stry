<?php

declare(strict_types=1);

namespace Domain\Groups\Actions;

use Domain\Groups\Models\Group;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CreateNewGroup
{
    public function execute(Model $model, array $attributes): Group
    {
        return DB::transaction(function () use ($model, $attributes) {
            $model = $model->groups()->firstOrCreate(
                Arr::only($attributes, ['name', 'type']),
                Arr::only($attributes, app(Group::class)->getFillable()),
            );

            return $model;
        });
    }
}
