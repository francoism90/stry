<?php

declare(strict_types=1);

namespace App\Api\Media\Resources;

use Domain\Media\Models\Transcode;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Transcode
 */
class TranscodeResource extends JsonResource
{
    /**
     * @var bool
     */
    public $preserveKeys = true;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->getRouteKey(),
            'preset' => $this->preset,
            'pending' => $this->isPending(),
            'processing' => $this->isProcessing(),
            'completed' => $this->isCompleted(),
            'failed' => $this->isFailed(),
            'state' => $this->state->toArray(),
            'file_size' => $this->getFileSize(),
            'file_size_human' => $this->getHumanReadableFileSize(),
            'error_message' => $this->error_message,
            'retry_count' => $this->retry_count,
            'started_at' => $this->started_at,
            'transcoded_at' => $this->transcoded_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
