<?php

declare(strict_types=1);

namespace Domain\Playlists\Jobs;

use DateTime;
use Domain\Playlists\Actions\SetPlaylistProgress;
use Domain\Playlists\Models\Playlist;
use Domain\Users\Models\User;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;

class RecordPlaylist implements ShouldQueueAfterCommit
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

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
        public User $user,
        public ?array $attributes = null,
    ) {}

    public function handle(): void
    {
        app(SetPlaylistProgress::class)->handle($this->playlist, $this->user, $this->attributes);
    }

    /**
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping($this->playlist->getKey()))->releaseAfter(5),
        ];
    }

    public function retryUntil(): DateTime
    {
        return now()->addMinutes(30);
    }
}
