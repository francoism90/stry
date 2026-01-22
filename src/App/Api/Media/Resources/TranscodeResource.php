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
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->getRouteKey(),
            'preset' => $this->preset,
            'state' => $this->state->getValue(),
            'error_message' => $this->error_message,
            'retry_count' => $this->retry_count,
            'started_at' => $this->started_at,
            'completed_at' => $this->completed_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
