<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Chapters\Models\Chapter;
use Domain\Videos\Models\Video;
use Foxws\Media\FFMpeg\Clip;
use Foxws\Media\FFMpeg\Scene;
use Foxws\Media\Opener;

/**
 * Picks the cuts of a reel: short parts of the scenes that change the most, spread over the video and
 * kept out of its opening and ending and of skippable chapters (intros, credits, sponsors and so on).
 * Videos without enough scene changes get evenly spaced cuts instead.
 */
class SelectReelClips
{
    public const int MAXIMUM_CUTS = 8;

    public const float CUT_DURATION = 4.0;

    public const float MAXIMUM_DURATION = 32.0;

    public const float MINIMUM_VIDEO_DURATION = 45.0;

    /**
     * Cuts start this far into their scene, past the transition.
     */
    protected const float SCENE_OFFSET = 0.5;

    /**
     * Cuts shorter than this are left out.
     */
    protected const float MINIMUM_CUT_DURATION = 1.5;

    /**
     * The part of the video at its start and end that's left out.
     */
    protected const float EDGE = 0.05;

    /**
     * @return list<Clip>
     */
    public function handle(Video $video, Opener $opener): array
    {
        $duration = $opener->probe()->duration();

        if ($duration < self::MINIMUM_VIDEO_DURATION) {
            return [];
        }

        $excluded = $this->excludedRanges($video, $duration);

        $cuts = $this->fromScenes($opener->scenes(), $duration, $excluded);

        if (count($cuts) < 3) {
            $cuts = $this->evenlySpaced($duration, $excluded);
        }

        usort($cuts, fn (Clip $a, Clip $b): int => $a->from <=> $b->from);

        return $cuts;
    }

    /**
     * @param  list<Scene>  $scenes
     * @param  list<array{float, float}>  $excluded
     * @return list<Clip>
     */
    protected function fromScenes(array $scenes, float $duration, array $excluded): array
    {
        $candidates = [];

        foreach ($scenes as $scene) {
            $cut = $this->cutOf($scene->start + self::SCENE_OFFSET, $scene->end, $excluded);

            if ($cut !== null) {
                $candidates[] = ['cut' => $cut, 'score' => $scene->score ?? 0.0];
            }
        }

        usort($candidates, fn (array $a, array $b): int => [$b['score'], $a['cut']->from] <=> [$a['score'], $b['cut']->from]);

        return $this->pick(array_column($candidates, 'cut'), $duration / 10);
    }

    /**
     * @param  list<array{float, float}>  $excluded
     * @return list<Clip>
     */
    protected function evenlySpaced(float $duration, array $excluded): array
    {
        $start = $duration * self::EDGE;
        $step = ($duration * (1 - 2 * self::EDGE)) / self::MAXIMUM_CUTS;

        $candidates = [];

        for ($index = 0; $index < self::MAXIMUM_CUTS; $index++) {
            $from = $start + $step * ($index + 0.5) - self::CUT_DURATION / 2;

            $candidates[] = $this->cutOf($from, $duration, $excluded);
        }

        return $this->pick(array_values(array_filter($candidates)), 0.0);
    }

    /**
     * @param  list<Clip>  $candidates
     * @return list<Clip>
     */
    protected function pick(array $candidates, float $spacing): array
    {
        $cuts = [];
        $total = 0.0;

        foreach ($candidates as $candidate) {
            if (count($cuts) >= self::MAXIMUM_CUTS || $total + $candidate->duration() > self::MAXIMUM_DURATION) {
                break;
            }

            foreach ($cuts as $cut) {
                if (abs($cut->from - $candidate->from) < $spacing) {
                    continue 2;
                }
            }

            $cuts[] = $candidate;
            $total += $candidate->duration();
        }

        return $cuts;
    }

    /**
     * A cut from the given time, at most CUT_DURATION long and ending before $end, or null when it would
     * be too short or overlap an excluded range.
     *
     * @param  list<array{float, float}>  $excluded
     */
    protected function cutOf(float $from, float $end, array $excluded): ?Clip
    {
        $to = min($from + self::CUT_DURATION, $end);

        if ($to - $from < self::MINIMUM_CUT_DURATION) {
            return null;
        }

        foreach ($excluded as [$start, $stop]) {
            if ($from < $stop && $to > $start) {
                return null;
            }
        }

        return Clip::make($from, $to);
    }

    /**
     * @return list<array{float, float}>
     */
    protected function excludedRanges(Video $video, float $duration): array
    {
        $chapters = $video->chapters
            ->filter(fn (Chapter $chapter): bool => $chapter->type->isSkippable())
            ->map(fn (Chapter $chapter): array => [(float) $chapter->start_time, (float) $chapter->end_time])
            ->values()
            ->all();

        return [
            [0.0, $duration * self::EDGE],
            [$duration * (1 - self::EDGE), $duration],
            ...$chapters,
        ];
    }
}
