<?php

declare(strict_types=1);

namespace Domain\Videos\Concerns;

use Domain\Media\Models\Media;
use Domain\Playlists\DataObjects\CaptionStream;
use Domain\Playlists\Enums\PlaylistType;
use Domain\Playlists\Models\Playlist;
use Domain\Playlists\Settings\PlaylistSettings;
use Domain\Videos\Models\Video;
use Foxws\Shaka\MediaOpener as ShakaMediaOpener;
use Foxws\Shaka\Support\EncryptionKey as ShakaEncryptionKey;
use Foxws\Streamer\MediaOpener as StreamerMediaOpener;
use Foxws\Streamer\Support\EncryptionKey as StreamerEncryptionKey;
use Illuminate\Support\Collection;
use Throwable;

/**
 * @property PlaylistSettings $settings
 */
trait CreatesVideoPlaylists
{
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

    protected function createPlaylist(
        Video $video,
        PlaylistType $type,
        ShakaEncryptionKey|StreamerEncryptionKey|null $encryptionKey = null,
    ): Playlist {
        /** @var Playlist */
        return $video->createPlaylist([
            'encryption_key_id' => $encryptionKey?->keyId,
            'encryption_key' => $encryptionKey?->key,
            'type' => $type,
            'dash_file_name' => 'index.mpd',
            'hls_file_name' => 'master.m3u8',
        ]);
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
