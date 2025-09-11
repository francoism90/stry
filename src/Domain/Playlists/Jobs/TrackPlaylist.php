<?php

declare(strict_types=1);

namespace Domain\Playlists\Jobs;

use Domain\Playlists\Actions\MarkPlaylistAsAccessed;
use Domain\Playlists\Models\Playlist;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Spatie\RateLimitedMiddleware\RateLimited;

class TrackPlaylist implements ShouldBeUnique, ShouldQueueAfterCommit
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var int
     */
    public $tries = 1;

    /**
     * @var int
     */
    public $timeout = 60 * 10;

    /**
     * @var int
     */
    public $maxExceptions = 1;

    /**
     * @var bool
     */
    public $failOnTimeout = true;

    /**
     * @var bool
     */
    public $deleteWhenMissingModels = true;

    public function __construct(
        public Playlist $playlist,
    ) {}

    public function handle(): void
    {
        app(MarkPlaylistAsAccessed::class)->handle($this->playlist);
    }

    /**
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping($this->playlist->getKey()))->dontRelease(),
            (new RateLimited)->allow(1)->everySeconds(60)->releaseAfterOneMinute(),
        ];
    }

    public function uniqueId(): string
    {
        return (string) $this->playlist->getKey();
    }
}
