<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Chapters\Enums\ChapterType;
use Domain\Chapters\Models\Chapter;
use Domain\Media\Models\Media;
use Domain\Playlists\Settings\PlaylistSettings;
use Domain\Videos\Models\Video;
use Foxws\Media\Delivery\DirectStream;
use Foxws\Media\Delivery\Marker;
use Foxws\Media\Encryption\EncryptionKey;
use Foxws\Media\MediaFactory;
use Illuminate\Support\Facades\Config;

/**
 * Streams the best clip of a video straight from its disk, with its captions as subtitle tracks,
 * its chapters as markers and a chapter track, and I-frames for trick play. With encryption on,
 * segments are encrypted per request with a key derived from the app key, so it doesn't have to
 * be stored.
 */
class CreateVideoDirectStream
{
    public function __construct(
        protected MediaFactory $media,
        protected PlaylistSettings $settings,
    ) {}

    public function handle(Video $video): DirectStream
    {
        $clip = $video->getClips()->firstOrFail();

        $stream = $this->media->fromDisk($clip->disk)
            ->open($clip->getPathRelativeToRoot())
            ->stream();

        if (filled($this->settings->encryption)) {
            $stream->withEncryption(EncryptionKey::derive(Config::string('app.key'), "video:{$video->getKey()}"));
        }

        if ($video->getCaptions()->isEmpty()) {
            $stream->withEmbeddedSubtitles();
        }

        $video->getCaptions()->each(fn (Media $caption) => $stream->withSubtitles(
            path: $caption->getPathRelativeToRoot(),
            language: $caption->getCustomProperty('language_code', $this->settings->text_language->value),
            label: $caption->name,
            disk: $caption->disk,
        ));

        return $stream->withThumbnails($clip->getThumbnails())
            ->withTrickPlay()
            ->chapterTrackFrom(null, ChapterType::MainEvent->label())
            ->withMarkers(array_values($video->chapters
                ->filter(fn (Chapter $chapter) => (float) $chapter->start_time >= 0 && (float) $chapter->end_time >= (float) $chapter->start_time)
                ->map(fn (Chapter $chapter) => new Marker(
                    start: (float) $chapter->start_time,
                    end: (float) $chapter->end_time,
                    title: $chapter->label,
                    class: $chapter->type->value,
                ))
                ->all()));
    }
}
