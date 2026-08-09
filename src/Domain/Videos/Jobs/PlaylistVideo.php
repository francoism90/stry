<?php

declare(strict_types=1);

namespace Domain\Videos\Jobs;

use Carbon\CarbonInterface;
use Domain\Playlists\Enums\PlaylistType;
use Domain\Playlists\Exceptions\PlaylistTypeException;
use Domain\Playlists\Models\Playlist;
use Domain\Videos\Actions\CreateNewVideoPlaylist;
use Domain\Videos\Actions\CreateNewVideoStream;
use Domain\Videos\Models\Video;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;

use function Illuminate\Support\enum_value;

class PlaylistVideo implements ShouldBeUniqueUntilProcessing, ShouldQueueAfterCommit
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var int
     */
    public $timeout = 14400;

    /**
     * @var int
     */
    public $uniqueFor = 1800;

    /**
     * @var int
     */
    public $maxExceptions = 4;

    /**
     * @var array<int, int>
     */
    public $backoff = [10, 30, 60, 90];

    /**
     * @var bool
     */
    public $failOnTimeout = true;

    /**
     * @var bool
     */
    public $deleteWhenMissingModels = true;

    public function __construct(
        public Video $video,
        public ?PlaylistType $type = null,
    ) {
        $this
            ->onConnection('redis-long')
            ->onQueue('transcoding');
    }

    public function handle(): void
    {
        // Determine the playlist type to create
        $type = $this->type ?? Playlist::getDefaultType();

        // Create the appropriate playlist based on the type
        match ($type) {
            PlaylistType::Packager => app(CreateNewVideoPlaylist::class)->handle($this->video),
            PlaylistType::Streamer => app(CreateNewVideoStream::class)->handle($this->video),
            default => throw PlaylistTypeException::invalidType($type),
        };
    }

    /**
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping($this->uniqueId()))
                ->expireAfter($this->timeout)
                ->releaseAfter(30),
        ];
    }

    public function retryUntil(): CarbonInterface
    {
        return now()->addSeconds($this->timeout);
    }

    public function uniqueId(): string
    {
        $type = enum_value($this->type, 'packager');

        return hash('xxh128', "{$this->video->getKey()}:{$type}");
    }
}
