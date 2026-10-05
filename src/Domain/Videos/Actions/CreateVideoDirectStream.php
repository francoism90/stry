<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Chapters\Enums\ChapterType;
use Domain\Media\Models\Media;
use Domain\Videos\Models\Video;
use Domain\Videos\Settings\PlaybackSettings;
use Domain\Videos\Settings\ProcessingSettings;
use Foxws\Media\Delivery\DirectStream;
use Foxws\Media\Encoding\Ladder;
use Foxws\Media\Encoding\Rendition;
use Foxws\Media\Encryption\EncryptionKey;
use Foxws\Media\MediaFactory;
use Illuminate\Support\Facades\Config;

/**
 * Streams the best clip of a video straight from its disk, with smaller renditions below it that
 * are encoded while they're watched, its captions as subtitle tracks, its chapters as markers and
 * a chapter track, and I-frames for trick play. With encryption on, segments are encrypted per
 * request with a key derived from the app key, so it doesn't have to be stored.
 */
class CreateVideoDirectStream
{
    public function __construct(
        protected MediaFactory $media,
        protected PlaybackSettings $settings,
        protected ProcessingSettings $processing,
    ) {}

    public function handle(Video $video): DirectStream
    {
        $clip = $video->getClips()->firstOrFail();

        $stream = $this->media->fromDisk($clip->disk)
            ->open($clip->getPathRelativeToRoot())
            ->stream()
            ->withRenditions($this->renditions());

        if ($this->settings->encryption) {
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
            ->withMarkers($video->getChapterMarkers());
    }

    /**
     * The renditions picked in the playback settings, at the standard ladder's bitrates, or none
     * when the processing settings don't create renditions.
     */
    protected function renditions(): ?Ladder
    {
        if (! $this->processing->create_renditions) {
            return null;
        }

        $renditions = array_values(array_filter(
            Ladder::standard()->renditions,
            fn (Rendition $rendition): bool => in_array($rendition->height, $this->settings->renditions, true),
        ));

        return $renditions !== [] ? new Ladder($renditions) : null;
    }
}
