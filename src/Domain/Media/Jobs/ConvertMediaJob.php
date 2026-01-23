<?php

declare(strict_types=1);

namespace Domain\Media\Jobs;

use Domain\Media\Actions\ConvertMediaToAv1;
use Domain\Media\Models\Transcode;
use Domain\Media\States;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ConvertMediaJob implements ShouldBeUnique, ShouldQueue, ShouldQueueAfterCommit
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 7200;

    public int $tries = 1;

    public int $maxExceptions = 1;

    public bool $failOnTimeout = true;

    public bool $deleteWhenMissingModels = true;

    public function __construct(
        public Transcode $transcode,
        public ?string $preset = null,
    ) {
        $this->onQueue('processing');
    }

    public function handle(ConvertMediaToAv1 $action): void
    {
        // Update state to processing
        $this->transcode->state->transitionTo(States\Processing::class);
        $this->transcode->touch('started_at');

        // Perform conversion
        try {
            $action->handle($this->transcode);

            // Update state to completed
            $this->transcode->state->transitionTo(States\Completed::class);
            $this->transcode->touch('transcoded_at');
        } catch (Throwable $e) {
            $this->transcode->state->transitionTo(States\Failed::class);

            // Update error message and increment retry count
            $this->transcode->updateOrFail([
                'error_message' => $e->getMessage(),
                'retry_count' => $this->transcode->retry_count + 1,
            ]);

            throw $e;
        }
    }

    /**
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping($this->transcode->media_id))->dontRelease(),
        ];
    }

    public function uniqueId(): string
    {
        return hash('xxh128', "transcode:{$this->transcode->media_id}");
    }
}
