<?php

declare(strict_types=1);

namespace Domain\Playlists\Jobs;

use Domain\Playlists\Actions\SyncPlaylistProgress;
use Domain\Playlists\Models\Playlist;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;

class SyncProgress implements ShouldQueue
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
    public $backoff = 3;

    /**
     * @var int
     */
    public $timeout = 60;

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
        public array $attributes = [],
    ) {
        $this->onQueue('processing');
    }

    public function handle(): void
    {
        app(SyncPlaylistProgress::class)->handle($this->playlist, $this->attributes);
    }

    /**
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping($this->playlist->getKey()))->releaseAfter(10),
        ];
    }
}
