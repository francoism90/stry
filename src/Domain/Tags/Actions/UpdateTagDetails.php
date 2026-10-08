<?php

declare(strict_types=1);

namespace Domain\Tags\Actions;

use Domain\Tags\Models\Tag;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class UpdateTagDetails
{
    public function __construct(protected SetTagsOrder $sorter) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(Tag $tag, array $attributes = []): void
    {
        DB::transaction(function () use ($tag, $attributes) {
            // Update the tag attributes
            $tag->updateOrFail(
                Arr::only($attributes, $tag->getFillable()),
            );

            // Sync related tags if provided
            if (array_key_exists('related', $attributes)) {
                $tagIds = Tag::query()->options(data_get($attributes, 'related.*.id', []))->get();

                $tag->syncRelated($tagIds);
            }

            // Keep tags in their natural display order
            $this->sorter->handle();
        });

        // Replace or remove the tag's own picture
        $avatar = data_get($attributes, 'avatar');

        if ($avatar instanceof UploadedFile) {
            $tag->addMedia($avatar)->toMediaCollection('avatar');
        } elseif (data_get($attributes, 'remove_avatar')) {
            $tag->clearMediaCollection('avatar');
        }
    }
}
