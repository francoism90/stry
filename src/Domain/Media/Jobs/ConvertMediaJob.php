<?php

declare(strict_types=1);

namespace Domain\Media\Jobs;

use Domain\Media\Actions\ConvertMediaToAv1;
use Domain\Media\Models\Transcode;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
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
        $this->transcode->update([
            'state' => 'processing',
            'started_at' => now(),
        ]);

        try {
            $action->handle($this->transcode, $this->preset);

            $this->transcode->update([
                'state' => 'completed',
                'progress' => 100,
                'completed_at' => now(),
            ]);
        } catch (Throwable $e) {
            $this->transcode->update([
                'state' => 'failed',
                'error_message' => $e->getMessage(),
                'retry_count' => $this->transcode->retry_count + 1,
            ]);

            Log::error('Media conversion failed', [
                'transcode_id' => $this->transcode->id,
                'video_id' => $this->transcode->video_id,
                'media_id' => $this->transcode->media_id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
