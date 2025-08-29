<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use App\Api\Videos\Resources\VideoResource;
use Domain\Videos\Collections\VideoCollection;
use Domain\Videos\Models\Video;
use Domain\Videos\States\Verified;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\LazyCollection;

class GetSimilarVideos
{
    public function handle(Video $video, int $limit = 20): ResourceCollection
    {
        $items = VideoCollection::make([
            ...$this->phrases($video),
            ...$this->tagged($video),
            ...$this->random($video),
        ]);

        return $items
            ->unique('id')
            ->take($limit)
            ->loadMissing('tags')
            ->toResourceCollection(VideoResource::class);
    }

    protected function phrases(Video $video): LazyCollection
    {
        // Split the video name into words and filter out common words
        $query = str((string) $video->name)
            ->title()
            ->matchAll('/[\p{L}\p{N}]+/u')
            ->reject(fn (string $word = '') => mb_strlen($word) < 2 || in_array(mb_strtolower($word), ['and', 'a', 'or']))
            ->take(10)
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
