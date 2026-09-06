<?php

declare(strict_types=1);

namespace Domain\Chapters\Actions;

use Domain\Chapters\Enums\ChapterFiller;
use Domain\Chapters\Models\Chapter;
use Domain\Videos\Models\Video;
use Illuminate\Support\Collection;

class GenerateChapterVtt
{
    public function handle(Video $video): string
    {
        $chapters = $video->chapters;

        /** @var Collection<int, string> $cues */
        $cues = $chapters->map(fn (Chapter $chapter) => $this->cue(
            $chapter->ulid,
            (float) $chapter->start_time,
            (float) $chapter->end_time,
            $chapter->label,
        ));

        $lastChapter = $chapters->last();
        $duration = $video->durationInSeconds();

        // Shaka's seek bar resolves the hover tooltip by matching whichever chapter is last in the
        // list for every timestamp past its own end time, all the way to the end of the video - it
        // has no concept of "past the last defined chapter." A trailing cue caps that bleed at the
        // real end of our chapters, instead of the last chapter's title covering the rest.
        if ($lastChapter && $duration > (float) $lastChapter->end_time) {
            $cues->push($this->cue(
                'chapters-end',
                (float) $lastChapter->end_time,
                $duration,
                ChapterFiller::MainEvent->label(),
            ));
        }

        return $cues->prepend('WEBVTT')->implode("\n\n");
    }

    protected function cue(string $id, float $start, float $end, string $text): string
    {
        $lines = [$id, sprintf('%s --> %s', $this->formatTimestamp($start), $this->formatTimestamp($end))];

        if ($text !== '') {
            $lines[] = $text;
        }

        return implode("\n", $lines);
    }

    protected function formatTimestamp(float $seconds): string
    {
        $milliseconds = (int) round($seconds * 1000);

        return sprintf(
            '%02d:%02d:%02d.%03d',
            intdiv($milliseconds, 3_600_000),
            intdiv($milliseconds % 3_600_000, 60_000),
            intdiv($milliseconds % 60_000, 1000),
            $milliseconds % 1000,
        );
    }
}
