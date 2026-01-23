<?php

declare(strict_types=1);

namespace Domain\Media\Models;

use Domain\Media\Collections\MediaCollection;
use Domain\Media\QueryBuilders\MediaQueryBuilder;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    protected function casts(): array
    {
        return [
            'manipulations' => 'array',
            'custom_properties' => 'array',
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

    public function transcodes(): HasMany
    {
        return $this->hasMany(Transcode::class);
    }
}
