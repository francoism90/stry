<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Media\Models\Media;
use Domain\Playlists\DataObjects\CaptionStream;
use Domain\Playlists\Enums\PlaylistType;
use Domain\Playlists\Models\Playlist;
use Domain\Playlists\Settings\PlaylistSettings;
use Domain\Videos\Models\Video;
use Foxws\Shaka\MediaOpener as ShakaMediaOpener;
use Foxws\Streamer\MediaOpener as StreamerMediaOpener;
use Illuminate\Support\Collection;
use Throwable;

abstract class VideoPlaylistAction
{
    public function __construct(
        protected PlaylistSettings $settings,
    ) {}

    /**
     * @return Collection<string, Playlist>
     */
    abstract public function handle(Video $video): Collection;

    protected function shouldCreatePlaylist(Video $video, PlaylistType $type): bool
    {
        return ! $video->hasPlaylist($type) && $video->hasMedia('clips');
    }

    /**
     * @return Collection<int, CaptionStream>
     */
    protected function getCaptionStreams(Video $video): Collection
    {
        return $video->getCaptions()->map(fn (Media $caption) => CaptionStream::from([
            'id' => $caption->getKey(),
            'disk' => $caption->disk,
            'path' => $caption->getPath(),
            'language' => $caption->getCustomProperty('language_code', $this->settings->text_language->value),
        ]));
    }

    /**
     * Export the packaged streams, marking the playlist as ready or failed accordingly.
     *
     * @throws Throwable
     */
    protected function exportPlaylist(ShakaMediaOpener|StreamerMediaOpener $opener, Playlist $playlist): void
    {
        try {
            $opener
                ->export()
                ->toDisk($playlist->getDisk())
                ->toPath($playlist->getPath())
                ->afterSaving(fn () => $playlist->markAsReady())
                ->save();
        } catch (Throwable $exception) {
            $playlist->markAsFailed();

            throw $exception;
        } finally {
            $opener->cleanupTemporaryFiles();
        }
    }
}
