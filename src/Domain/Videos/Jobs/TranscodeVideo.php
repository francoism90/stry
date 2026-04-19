<?php

declare(strict_types=1);

namespace Domain\Videos\Jobs;

use Domain\Videos\Actions\CreateNewVideoTranscode;
use Domain\Videos\Models\Video;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;

class TranscodeVideo implements ShouldBeUniqueUntilProcessing, ShouldQueueAfterCommit
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
    public $timeout = 14400;

    /**
     * @var int
     */
    public $uniqueFor = 1800;

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
    ) {
        $this->onQueue('transcoding');
    }

    public function handle(): void
    {
        app(CreateNewVideoTranscode::class)->handle($this->video);
    }

    /**
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping($this->uniqueId()))->dontRelease(),
        ];
    }

    public function uniqueId(): string
    {
        return (string) $this->video->getKey();
    }
}
