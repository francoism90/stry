<?php

declare(strict_types=1);

namespace Domain\Videos\Jobs;

use Carbon\CarbonInterface;
use Domain\Videos\Actions\CreateClipRenditions;
use Domain\Videos\Models\Video;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;

class RenderVideoRenditions implements ShouldBeUniqueUntilProcessing, ShouldQueueAfterCommit
{
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
    public $maxExceptions = 2;

    /**
     * @var array<int, int>
     */
    public $backoff = [60, 300];

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
    ) {
        $this
            ->onConnection('redis-long')
            ->onQueue('processing');
    }

    public function handle(CreateClipRenditions $renditions): void
    {
        $clip = $this->video->getClips()->first();

        if ($clip !== null) {
            $renditions->handle($clip);
        }
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
