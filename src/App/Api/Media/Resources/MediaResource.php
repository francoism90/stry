<?php

declare(strict_types=1);

namespace App\Api\Media\Resources;

use Domain\Media\Models\Media;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Media
 */
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
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'file_size' => $this->human_readable_size,
            'collection_name' => $this->collection_name,
            'disk' => $this->disk,
            'conversions_disk' => $this->conversions_disk,
            'url' => $this->asset_uri,
            'codec' => $this->codec,
            'resolution' => $this->resolution,
            'bitrate' => $this->bitrate,
            'custom_properties' => $this->custom_properties,
            'generated_conversions' => $this->generated_conversions,
            'responsive_images' => $this->responsive_images,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
