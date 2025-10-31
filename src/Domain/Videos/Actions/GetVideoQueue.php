<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Videos\Collections\VideoCollection;
use Domain\Videos\Models\Video;
use Domain\Videos\States\Verified;
use Illuminate\Support\LazyCollection;

class GetVideoQueue
{
    public function handle(Video $video, ?int $limit = null): VideoCollection
    {
        return VideoCollection::make([
            ...$this->phrases($video),
            ...$this->tagged($video),
            ...$this->random($video),
        ])->unique('id')->take($limit ?? 9);
    }

    protected function phrases(Video $video): LazyCollection
    {
        // Split the video name into words and filter out common words
        $query = str((string) $video->name)
            ->title()
            ->matchAll('/[\p{L}\p{N}]+/u')
            ->reject(fn (string $word = '') => mb_strlen($word) < 2 || in_array(mb_strtolower($word), ['and', 'a', 'or']))
            ->take(8)
            ->merge([$video->identifier])
            ->filter()
            ->unique();

        // Generate phrases from the words
        $items = LazyCollection::make(function () use ($query) {
            // e.g. foo bar 1, foo bar, foo
            $words = $query->count();

            // Generate phrases by decreasing word count
            for ($i = $words; $i > 0; $i--) {
                $phrase = (string) $query->take($i)->implode(' ');

                yield Video::search($phrase)
                    ->where('state', Verified::$name)
                    ->take(6)
                    ->cursor();
            }
        });

        return $items
            ->flatten()
            ->reject(fn (Video $item) => $item->is($video))
            ->take(9)
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
            ->take(9)
            ->cursor();
    }

    protected function random(Video $video): LazyCollection
    {
        return Video::query()
            ->verified()
            ->whereKeyNot($video)
            ->take(9)
            ->cursor();
    }
}
