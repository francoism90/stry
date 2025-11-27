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
            'file_name' => $this->file_name,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'file_size' => $this->human_readable_size,
            'collection_name' => $this->collection_name,
            'disk' => $this->disk,
            'conversions_disk' => $this->conversions_disk,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
