<?php

declare(strict_types=1);

namespace Domain\Media\Models;

use Domain\Media\Collections\MediaCollection;
use Domain\Media\QueryBuilders\MediaQueryBuilder;
use Illuminate\Broadcasting\Channel;
use Illuminate\Database\Eloquent\BroadcastsEvents;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Arr;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
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
     * @return array{codec_type?: string, codec_name?: string, width?: int, height?: int, bit_rate?: int}
     */
    public function getVideoStream(): array
    {
        return $this->videoStream;
    }

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(string $event): array
    {
        if ($event === 'updated') {
            return [];
        }

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

    public function broadcastAfterCommit(): bool
    {
        return true;
    }

    protected function assetUri(): Attribute
    {
        return Attribute::make(
            get: fn (): string => route('actions.media.download', $this),
        )->shouldCache();
    }

    protected function videoStream(): Attribute
    {
        return Attribute::make(
            get: fn (): array => Arr::first($this->getStreams(), fn (array $stream) => ($stream['codec_type'] ?? null) === 'video') ?? [],
        )->shouldCache();
    }

    protected function codec(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => isset($this->videoStream['codec_name']) ? Str::upper($this->videoStream['codec_name']) : null,
        )->shouldCache();
    }

    protected function resolution(): Attribute
    {
        return Attribute::make(
            get: function (): ?string {
                $stream = $this->videoStream;

                return isset($stream['width'], $stream['height'])
                    ? sprintf('%d×%d', $stream['width'], $stream['height'])
                    : null;
            },
        )->shouldCache();
    }

    protected function bitrate(): Attribute
    {
        return Attribute::make(
            get: function (): ?string {
                $bitRate = $this->videoStream['bit_rate'] ?? null;

                return $bitRate !== null ? sprintf('%dkbps', (int) round((int) $bitRate / 1000)) : null;
            },
        )->shouldCache();
    }
}
