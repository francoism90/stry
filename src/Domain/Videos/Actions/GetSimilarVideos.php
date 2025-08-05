<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use App\Api\Videos\Resources\VideoResource;
use Domain\Tags\Models\Tag;
use Domain\Videos\Models\Video;
use Domain\Videos\States\Verified;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\LazyCollection;

class GetSimilarVideos
{
    public function handle(Video $video, int $limit = 16): ResourceCollection
    {
        return collect([
            ...$this->phrases($video),
            ...$this->tagged($video),
            ...$this->random($video),
        ])->unique()->take($limit)->toResourceCollection(VideoResource::class);
    }

    protected function phrases(Video $video): LazyCollection
    {
        $query = str($video->name)
            ->title()
            ->matchAll('/[\p{L}\p{N}]+/u')
            ->reject(fn (string $word) => in_array(mb_strtolower($word), ['and', 'a', 'or']))
            ->take(7)
            ->merge([$video->identifier])
            ->filter()
            ->unique();

        $items = LazyCollection::make(function () use ($query) {
            // e.g. foo bar 1, foo bar, foo
            for ($i = $query->count(); $i >= 1; $i--) {
                $phrase = (string) $query->take($i)->implode(' ');

                yield Video::search($phrase)
                    ->where('state', Verified::$name)
                    ->take(7)
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
        $relatables = $video->tags
            ->loadMissing('relatables')
            ->flatMap(fn (Tag $tag) => $tag->related)
            ->unique()
            ->all();

        return Video::query()
            ->verified()
            ->withAnyTagsOfAnyType([
                ...$video->tags,
                ...$relatables,
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
