<?php

declare(strict_types=1);

namespace Domain\Videos\Jobs;

use Carbon\CarbonInterface;
use Domain\Users\Models\User;
use Domain\Videos\Actions\MoveVideoMedia;
use Domain\Videos\Models\Video;
use Domain\Videos\Notifications\VideoMovedNotification;
use Domain\Videos\Notifications\VideoMoveFailedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Throwable;

class MoveVideo implements ShouldBeUniqueUntilProcessing, ShouldQueueAfterCommit
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var int
     */
    public $timeout = 3600;

    /**
     * @var int
     */
    public $uniqueFor = 1800;

    /**
     * @var int
     */
    public $maxExceptions = 3;

    /**
     * @var array<int, int>
     */
    public $backoff = [10, 30, 60];

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
        public User $user,
        public string $disk,
    ) {
        $this
            ->onConnection('redis-long')
            ->onQueue('processing');
    }

    public function handle(): void
    {
        app(MoveVideoMedia::class)->handle($this->video, $this->disk);

        WarmVideoStream::dispatch($this->video);

        $this->user->notify(new VideoMovedNotification($this->video));
    }

    public function failed(?Throwable $exception): void
    {
        $this->user->notify(new VideoMoveFailedNotification($this->video));
    }

    public function retryUntil(): CarbonInterface
    {
        return now()->addSeconds($this->timeout);
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

    public function uniqueId(): string
    {
        return (string) $this->video->getKey();
    }
}
