<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Videos\Collections\VideoCollection;
use Domain\Videos\Models\Video;
use Domain\Videos\States\Verified;
use Illuminate\Support\LazyCollection;

class GetSimilarVideos
{
    public function handle(Video $video, int $limit = 16): VideoCollection
    {
        return VideoCollection::make([
            ...$this->phrases($video),
            ...$this->tagged($video),
            ...$this->random($video),
        ])->loadMissing('tags')->unique('id')->take($limit);
    }

    protected function phrases(Video $video): LazyCollection
    {
        // Split the video name into words and filter out common words
        $query = str($video->name)
            ->title()
            ->matchAll('/[\p{L}\p{N}]+/u')
            ->reject(fn (string $word = '') => str($word)->contains(['and', 'a', 'or'], true))
            ->take(6)
            ->merge([$video->identifier])
            ->filter()
            ->unique();

        $items = LazyCollection::make(function () use ($query) {
            // e.g. foo bar 1, foo bar, foo
            $words = $query->count();

            for ($i = $words; $i > 0; $i--) {
                $phrase = (string) $query->take($i)->implode(' ');

                yield Video::search($phrase)
                    ->where('state', Verified::$name)
                    ->take(8)
                    ->cursor();
            }
        });

        return $items
            ->flatten()
            ->reject(fn (Video $item) => $item->is($video))
            ->take(16)
            ->unique();
    }

    protected function tagged(Video $video): LazyCollection
    {
        return Video::query()
            ->verified()
            ->withAnyTagsOfAnyType([
                ...$video->tags,
                ...$video->tags->relates()->all(),
            ])
            ->whereKeyNot($video)
            ->inRandomOrder()
            ->take(16)
            ->cursor();
    }

    protected function random(Video $video): LazyCollection
    {
        return Video::query()
            ->verified()
            ->whereKeyNot($video)
            ->take(16)
            ->cursor();
    }
}
