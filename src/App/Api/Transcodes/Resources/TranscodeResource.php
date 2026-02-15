<?php

declare(strict_types=1);

namespace App\Api\Transcodes\Resources;

use Domain\Transcodes\Models\Transcode;
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

    public function toArray($request): array
    {
        return [
            'id' => $this->getRouteKey(),
            'encoder' => $this->encoder,
            'size' => $this->file_size,
            'file_size' => $this->human_file_size,
            'failed' => $this->isFailed(),
            'completed' => $this->isCompleted(),
            'processing' => $this->isProcessing(),
            'state' => $this->state->toArray(),
            'started_at' => $this->started_at?->toDateTimeString(),
            'transcoded_at' => $this->transcoded_at?->toDateTimeString(),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
