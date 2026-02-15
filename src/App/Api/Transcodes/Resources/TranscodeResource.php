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
            'state' => $this->state->toArray(),
            'expires_at' => $this->expires_at?->toDateTimeString(),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
