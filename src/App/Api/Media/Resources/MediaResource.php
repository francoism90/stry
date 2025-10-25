<?php

declare(strict_types=1);

namespace App\Api\Media\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{
    /**
     * @var bool
     */
    public $preserveKeys = true;

    public function toArray($request): array
    {
        return [
            'id' => $this->getRouteKey(),
            'asset' => $this->asset,
            'name' => $this->name,
            'mime_type' => $this->mime_type,
            'file_size' => $this->human_readable_size,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
