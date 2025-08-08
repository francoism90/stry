<?php

declare(strict_types=1);

namespace Domain\Playlists\Jobs;

use Domain\Playlists\Actions\CreateHlsPlaylist;
use Domain\Playlists\Models\Playlist;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessPlaylist implements ShouldBeUnique, ShouldQueueAfterCommit
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
    public $timeout = 60 * 60 * 4;

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
        public string $disk,
        public string $path,
    ) {
        $this->onQueue('processing');
    }

    public function handle(): void
    {
        app(CreateHlsPlaylist::class)->handle($this->playlist, $this->disk, $this->path);
    }

    public function uniqueId(): string
    {
        return hash('xxh128', implode(':', [$this->disk, $this->path]));
    }
}
