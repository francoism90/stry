<?php

declare(strict_types=1);

namespace Domain\Playlists\Jobs;

use Domain\Playlists\Actions\UpdatePlaylistActivity;
use Domain\Playlists\Models\Playlist;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\Skip;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;

class UpdateActivity implements ShouldBeUnique, ShouldQueue
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
    public $timeout = 60 * 5;

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
    ) {
        $this->onQueue('processing');
    }

    public function handle(): void
    {
        app(UpdatePlaylistActivity::class)->handle($this->playlist);
    }

    /**
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping($this->playlist->getKey()))->dontRelease(),
            (new Skip($this->playlist->accessed_at?->lt(now()->subMinutes(10)))),
        ];
    }

    public function uniqueId(): string
    {
        return (string) $this->playlist->getKey();
    }
}
