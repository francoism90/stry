<?php

declare(strict_types=1);

namespace Domain\Media\Models;

use Domain\Media\Collections\MediaCollection;
use Domain\Media\QueryBuilders\MediaQueryBuilder;
use Illuminate\Broadcasting\Channel;
use Illuminate\Database\Eloquent\BroadcastsEvents;
use Illuminate\Support\Collection;
use Illuminate\Support\Number;
use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;

class Media extends BaseMedia
{
    use BroadcastsEvents;

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

    public static function totalUsage(): string
    {
        return Number::fileSize(Media::query()->totalSize());
    }

    public function getStreams(): array
    {
        return $this->getCustomProperty('streams', []);
    }

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(string $event): array
    {
        return array_filter([$this, $this->model]);
    }

    public function broadcastChannel(): string
    {
        return 'media.'.$this->getRouteKey();
    }

    public function broadcastAs(string $event): string
    {
        return "media.{$event}";
    }

    public function broadcastWith(string $event): array
    {
        return ['id' => $this->getRouteKey()];
    }

    public function broadcastWhen(): bool
    {
        if ($this->wasRecentlyCreated) {
            return true;
        }

        return Collection::make($this->getChanges())
            ->keys()
            ->diff(['generated_conversions', 'responsive_images', 'updated_at'])
            ->isNotEmpty();
    }

    public function broadcastQueue(): string
    {
        return 'broadcasts';
    }

    public function broadcastAfterCommit(): bool
    {
        return true;
    }
}
