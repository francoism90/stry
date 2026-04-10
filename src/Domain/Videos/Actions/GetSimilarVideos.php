<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Tags\Collections\TagCollection;
use Domain\Videos\Collections\VideoCollection;
use Domain\Videos\Models\Video;
use Domain\Videos\QueryBuilders\VideoQueryBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Str;

class GetSimilarVideos
{
    public function handle(Video $video, ?int $limit = null): VideoCollection
    {
        $collect = VideoCollection::make();

        return $collect->merge([
            ...$this->phraseMatches($video),
            ...$this->tagMatches($video),
            ...$this->randomCandidates($video),
        ])->unique('id')->take($limit ?? 18);
    }

    /**
     * @return LazyCollection<int, Video>
     */
    protected function phraseMatches(Video $video): LazyCollection
    {
        // Initialize an empty collection for candidates
        $candidates = LazyCollection::make();

        // Extract meaningful tokens from the video
        $query = $this->extractMeaningfulTokens($video);

        // e.g. foo bar 1, foo bar, foo
        for ($i = $query->count(); $i > 0; $i--) {
            // Generate phrase by decreasing word count
            $phrase = (string) $query->take($i)->implode(' ');

            if (blank($phrase)) {
                continue;
            }

            // Get IDs to exclude (original video + already found candidates)
            $excludeIds = $candidates
                ->pluck('id')
                ->prepend($video->getKey())
                ->unique()
                ->all();

            // Search for videos matching the phrase
            $results = Video::search($phrase)
                ->query(fn (VideoQueryBuilder $query) => $query->with('tags'))
                ->whereNotIn('id', $excludeIds)
                ->where('state', 'verified')
                ->take(6)
                ->cursor();

            // Merge results into candidates
            $candidates = $candidates->merge($results);
        }

        return $candidates->unique('id')->take($this->limit ?? 18);
    }

    /**
     * @return LazyCollection<int, Video>
     */
    protected function tagMatches(Video $video): LazyCollection
    {
        // Initialize an empty collection for candidates
        $candidates = LazyCollection::make();

        /** @var TagCollection $tags */
        $tags = $video->tags;

        if ($tags->isEmpty()) {
            return $candidates;
        }

        // Find videos sharing tags with the given video
        return Video::query()
            ->whereKeyNot($video)
            ->with('tags')
            ->verified()
            ->withAnyTagsOfAnyType([
                ...$tags,
                ...$tags->relates()->all(),
            ])
            ->inRandomOrder()
            ->take($this->limit ?? 18)
            ->cursor();
    }

    /**
     * @return LazyCollection<int, Video>
     */
    protected function randomCandidates(Video $video): LazyCollection
    {
        return Video::query()
            ->whereKeyNot($video)
            ->verified()
            ->inRandomOrder()
            ->take($this->limit ?? 18)
            ->cursor();
    }

    /**
     * @return Collection<int, string>
     */
    protected function extractMeaningfulTokens(Video $video): Collection
    {
        $tokens = Str::of((string) $video->title)
            ->matchAll('/[\p{L}\p{N}]+/u')
            ->map(fn (string $word) => Str::lower($word))
            ->reject(fn (string $word) => $this->isCommonWord($word))
            ->take(8)
            ->values();

        return $tokens->unique()->values();
    }

    protected function isCommonWord(string $word = ''): bool
    {
        // List of common words to exclude
        $commonWords = Config::array('scout.common_words', [
            'a', 'an', 'the', 'and', 'or', 'of', 'in', 'to',
        ]);

        return in_array($word, $commonWords, true);
    }
}
