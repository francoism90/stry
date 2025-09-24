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
            'name' => $this->name,
            'file_name' => $this->file_name,
            'file_size' => $this->human_readable_size,
            'mime_type' => $this->mime_type,
            'asset' => $this->getUrl(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
