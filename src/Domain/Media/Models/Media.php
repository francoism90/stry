<?php

declare(strict_types=1);

namespace Domain\Media\Models;

use Domain\Media\Collections\MediaCollection;
use Domain\Media\QueryBuilders\MediaQueryBuilder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;

class Media extends BaseMedia
{
    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'model_id',
        'model_type',
        'uuid',
        'name',
        'file_name',
        'mime_type',
        'collection_name',
        'disk',
        'conversions_disk',
        'size',
        'manipulations',
        'custom_properties',
        'generated_conversions',
        'responsive_images',
        'order_column',
    ];

    /**
     * @var array<int, string>
     */
    protected $with = [
        'model',
    ];

    protected function casts(): array
    {
        return [
            'manipulations' => 'array',
            'custom_properties' => 'json',
            'generated_conversions' => 'array',
            'responsive_images' => 'array',
        ];
    }

    public function newEloquentBuilder($query): MediaQueryBuilder
    {
        return new MediaQueryBuilder($query);
    }

    public function newCollection(array $models = []): MediaCollection
    {
        return new MediaCollection($models);
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => (string) $this->getScoutKey(),
            'name' => (string) $this->name,
            'file_name' => (string) $this->file_name,
            'mime_type' => (string) $this->mime_type,
            'collection_name' => (string) $this->collection_name,
            'disk' => (string) $this->disk,
            'conversions_disk' => (string) $this->conversions_disk,
            'size' => (int) $this->size,
            'order' => (int) $this->order_column,
            'created_at' => (int) $this->created_at->timestamp,
            'updated_at' => (int) $this->updated_at->timestamp,
        ];
    }

    protected function asset(): Attribute
    {
        return Attribute::make(
            get: fn () => rescue(fn () => $this->getTemporaryUrl()),
        )->shouldCache();
    }
}
