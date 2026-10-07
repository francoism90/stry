<?php

declare(strict_types=1);

namespace Domain\Videos\Jobs;

use Domain\Videos\Actions\CreateVideoReel;
use Domain\Videos\Models\Video;
use Foxws\Media\Exceptions\ProcessFailedException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;

class GenerateVideoReel implements ShouldBeUniqueUntilProcessing, ShouldQueueAfterCommit
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var int
     */
    public $timeout = 1800;

    /**
     * @var int
     */
    public $tries = 3;

    /**
     * @var int
     */
    public $uniqueFor = 1800;

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

    public function handle(CreateVideoReel $action): void
    {
        try {
            $action->handle($this->video);
        } catch (ProcessFailedException $exception) {
            if (! $exception->isRetryable()) {
                $this->fail($exception);

                return;
            }

            $this->release(60);
        }
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
