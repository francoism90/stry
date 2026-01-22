<?php

declare(strict_types=1);

namespace Domain\Media\Jobs;

use Domain\Media\Actions\ConvertMediaToAv1;
use Domain\Media\Models\Transcode;
use Domain\Media\States;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ConvertMediaJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 7200;

    public function __construct(
        public Transcode $transcode,
        public ?string $preset = null,
    ) {
        $this->onQueue('processing');
    }

    public function handle(ConvertMediaToAv1 $action): void
    {
        $this->transcode->state->transitionTo(States\Processing::class);

        $this->transcode->updateOrFail(['started_at' => now()]);

        try {
            $action->handle($this->transcode);

            $this->transcode->state->transitionTo(States\Completed::class);
            $this->transcode->updateOrFail(['completed_at' => now()]);
        } catch (Throwable $e) {
            $this->transcode->state->transitionTo(States\Failed::class);

            $this->transcode->updateOrFail([
                'error_message' => $e->getMessage(),
                'retry_count' => $this->transcode->retry_count + 1,
            ]);

            throw $e;
        }
    }
}
