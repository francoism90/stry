<?php

declare(strict_types=1);

namespace Domain\Videos\Concerns;

use Domain\Playlists\Settings\PlaylistSettings;
use Foxws\Media\Facades\MediaStream;
use Illuminate\Support\Facades\Config;

/**
 * Plays the video straight from its best clip through the "videos" media stream, with signed
 * URLs that the player renews before they expire.
 */
trait InteractsWithDirectPlay
{
    public function canDirectPlay(): bool
    {
        return $this->hasMedia('clips');
    }

    /**
     * The signed URL of the DASH manifest.
     */
    public function getDirectPlayDashUrl(): string
    {
        return MediaStream::dashUrl('videos', ['video' => $this]);
    }

    /**
     * The signed URL of the HLS playlist with fragmented MP4 (CMAF) segments.
     */
    public function getDirectPlayHlsUrl(): string
    {
        return MediaStream::url('videos', ['video' => $this]);
    }

    /**
     * The signed URL of the chapters as WebVTT, for the player's seek bar.
     */
    public function getDirectPlayChaptersUrl(): string
    {
        return MediaStream::chaptersUrl('videos', ['video' => $this]);
    }

    /**
     * Seconds until the player should fetch new URLs, some time before the signatures expire.
     */
    public function getDirectPlayRefreshIn(): int
    {
        $lifetime = Config::integer('media.delivery.url_lifetime', 3600);

        return max($lifetime - app(PlaylistSettings::class)->manifest_refresh_before, 0);
    }
}
