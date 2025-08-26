<?php

declare(strict_types=1);

namespace Domain\Videos\Jobs;

use DateTime;
use Domain\Videos\Actions\CreateVideoCaptions;
use Domain\Videos\Actions\CreateVideoPreview;
use Domain\Videos\Actions\CreateVideoThumbnail;
use Domain\Videos\Actions\MarkVideoAsVerified;
use Domain\Videos\Models\Video;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Pipeline;

class ProcessVideo
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var int
     */
    public $timeout = 60 * 60 * 8;

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
        $this->onQueue('processing');
    }

    public function handle(): void
    {
        Pipeline::send($this->video)
            ->through([
                CreateVideoCaptions::class,
                CreateVideoThumbnail::class,
                CreateVideoPreview::class,
                MarkVideoAsVerified::class,
            ])
            ->thenReturn();
    }

    /**
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping($this->video->getKey()))->releaseAfter(30),
        ];
    }

    public function retryUntil(): DateTime
    {
        return now()->addMinutes(30);
    }
}
