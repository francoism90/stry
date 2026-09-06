<?php

declare(strict_types=1);

namespace Domain\Chapters\Actions;

use Domain\Chapters\Models\Chapter;
use Domain\Videos\Models\Video;

class GenerateChapterVtt
{
    public function handle(Video $video): string
    {
        $cues = $video->chapters->map(fn (Chapter $chapter) => sprintf(
            "%s\n%s --> %s\n%s",
            $chapter->ulid,
            $this->formatTimestamp((float) $chapter->start_time),
            $this->formatTimestamp((float) $chapter->end_time),
            $chapter->label,
        ));

        return $cues->prepend('WEBVTT')->implode("\n\n");
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
