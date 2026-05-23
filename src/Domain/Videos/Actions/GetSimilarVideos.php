<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Tags\Collections\TagCollection;
use Domain\Videos\Collections\VideoCollection;
use Domain\Videos\Models\Video;
use Domain\Videos\QueryBuilders\VideoQueryBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class GetSimilarVideos
{
    public function handle(Video $video, int $limit = 10): VideoCollection
    {
        $collect = VideoCollection::make();

        return $collect->merge([
            ...$this->seriesMatches($video, $limit),
            ...$this->phraseMatches($video, $limit),
            ...$this->tagMatches($video, $limit),
            ...$this->randomCandidates($video, $limit),
        ])->unique('id')->take($limit);
    }

    /**
     * @return Collection<int, Video>
     */
    protected function seriesMatches(Video $video, int $limit = 10): Collection
    {
        // Get the current locale
        $locale = App::currentLocale();

        // Get the video name in the current locale
        $name = $video->getTranslation('name', $locale);

        if (blank($name)) {
            return Collection::make();
        }

        // Find videos with the same name in the current locale, excluding the original video
        return Video::query()
            ->whereKeyNot($video)
            ->whereJsonContainsLocale('name', $locale, $name)
            ->with('tags')
            ->verified()
            ->orderBy('season')
            ->orderBy('episode')
            ->orderBy('part')
            ->take($limit)
            ->get();
    }

    /**
     * @return Collection<int, Video>
     */
    protected function phraseMatches(Video $video, int $limit = 10): Collection
    {
        // Initialize an empty collection for candidates
        $candidates = Collection::make();

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
                ->take(8)
                ->get();

            // Merge results into candidates
            $candidates = $candidates->merge($results);
        }

        return $candidates->unique('id')->take($limit);
    }

    /**
     * @return Collection<int, Video>
     */
    protected function tagMatches(Video $video, int $limit = 10): Collection
    {
        /** @var TagCollection $tags */
        $tags = $video->tags;

        if ($tags->isEmpty()) {
            return Collection::make();
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
            ->take($limit)
            ->get();
    }

    /**
     * @return Collection<int, Video>
     */
    protected function randomCandidates(Video $video, int $limit = 10): Collection
    {
        return Video::query()
            ->whereKeyNot($video)
            ->verified()
            ->inRandomOrder()
            ->take($limit)
            ->get();
    }

    /**
     * @return Collection<int, string>
     */
    protected function extractMeaningfulTokens(Video $video, int $limit = 14): Collection
    {
        // List of common words to exclude
        $commonWords = Config::array('videos.common_words');

        $tokens = Str::of((string) $video->name)
            ->matchAll('/[\p{L}\p{N}]+/u')
            ->map(fn (string $word): string => Str::lower($word))
            ->reject(fn (string $word): bool => in_array($word, $commonWords, true))
            ->take($limit)
            ->values();

        return $tokens->unique()->values();
    }
}
