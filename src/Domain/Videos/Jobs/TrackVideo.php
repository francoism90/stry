<?php

declare(strict_types=1);

namespace Domain\Videos\Jobs;

use DateTime;
use Domain\Users\Models\User;
use Domain\Videos\Actions\MarkVideoAsViewed;
use Domain\Videos\Models\Video;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Spatie\RateLimitedMiddleware\RateLimited;

class TrackVideo implements ShouldQueueAfterCommit
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

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
        public Video $video,
        public User $user,
        public ?array $attributes = null,
    ) {}

    public function handle(): void
    {
        app(MarkVideoAsViewed::class)->handle($this->video, $this->user, $this->attributes);
    }

    /**
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping($this->video->getKey()))->releaseAfter(10),
            (new RateLimited)->allow(1)->everySeconds(30)->releaseAfterOneMinute(),
        ];
    }

    public function retryUntil(): DateTime
    {
        return now()->addDay();
    }
}
