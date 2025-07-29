<?php

declare(strict_types=1);

namespace Domain\Tags\Actions;

use Domain\Tags\Models\Tag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SyncModelTags
{
    public function handle(Model $model, array $items = []): void
    {
        DB::transaction(function () use ($model, $items) {
            $tags = Tag::whereIn('ulid', $items)->get();

            $model->syncTags($tags);
        });
    }
}
