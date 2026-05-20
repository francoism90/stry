<?php

declare(strict_types=1);

namespace Domain\Profiles\Models;

use Database\Factories\ProfileFactory;
use Domain\Media\Concerns\InteractsWithMedia;
use Domain\Profiles\Collections\ProfileCollection;
use Domain\Profiles\Exceptions\CurrentProfileException;
use Domain\Profiles\QueryBuilders\ProfileQueryBuilder;
use Domain\Profiles\States\ProfileState;
use Domain\Profiles\Support\CurrentProfileContext;
use Domain\Shared\Casts\AsDateTime;
use Domain\Users\Concerns\InteractsWithUser;
use Illuminate\Database\Eloquent\BroadcastsEvents;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\ModelStates\HasStates;
use Support\MediaLibrary\MediaTemporaryUrl;

class Profile extends Model implements HasMedia
{
    use BroadcastsEvents;
    use HasFactory;
    use HasStates;
    use HasUlids;
    use InteractsWithMedia;
    use InteractsWithUser;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'avatar',
        'is_kids',
        'is_primary',
        'state',
        'settings',
    ];

    /**
     * @var array<int, string>
     */
    protected $hidden = [
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'is_kids' => 'boolean',
            'is_primary' => 'boolean',
            'state' => ProfileState::class,
            'settings' => AsArrayObject::class,
            'created_at' => AsDateTime::class,
            'updated_at' => AsDateTime::class,
        ];
    }

    protected static function newFactory(): ProfileFactory
    {
        return ProfileFactory::new();
    }

    public function newEloquentBuilder($query): ProfileQueryBuilder
    {
        return new ProfileQueryBuilder($query);
    }

    public function newCollection(array $models = []): ProfileCollection
    {
        return new ProfileCollection($models);
    }

    public function uniqueIds(): array
    {
        return ['ulid'];
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('avatar')
            ->useDisk('conversions')
            ->storeConversionsOnDisk('conversions')
            ->singleFile()
            ->withResponsiveImages()
            ->acceptsMimeTypes([
                'image/avif',
                'image/gif',
                'image/jpeg',
                'image/jpg',
                'image/png',
                'image/svg+xml',
                'image/tiff',
                'image/webp',
            ]);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this
            ->addMediaConversion('thumb')
            ->fit(Fit::Stretch, 1280, 720)
            ->sharpen(10);
    }

    public static function findFromUlid(Profile|string $value): ?Profile
    {
        if ($value instanceof Profile) {
            return $value;
        }

        return Profile::query()->firstWhere('ulid', $value);
    }

    public static function current(): ?Profile
    {
        return app(CurrentProfileContext::class)->get();
    }

    public static function currentOrFail(): Profile
    {
        $profile = static::current();

        if (! $profile) {
            throw CurrentProfileException::notAvailable();
        }

        return $profile;
    }

    public static function setCurrent(?Profile $profile): void
    {
        app(CurrentProfileContext::class)->set($profile);
    }

    public static function forgetCurrent(): void
    {
        app(CurrentProfileContext::class)->forget();
    }

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(string $event): array
    {
        return array_filter([$this, $this->user]);
    }

    public function broadcastChannel(): string
    {
        return 'profiles.'.$this->getRouteKey();
    }

    public function broadcastAs(string $event): string
    {
        return "profile.{$event}";
    }

    public function broadcastWith(string $event): array
    {
        return ['id' => $this->getRouteKey()];
    }

    public function broadcastAfterCommit(): bool
    {
        return true;
    }

    public function broadcastQueue(): string
    {
        return 'broadcasts';
    }

    public function isKids(): bool
    {
        return $this->is_kids;
    }

    public function isPrimary(): bool
    {
        return $this->is_primary;
    }

    public function thumbnailUrl(): ?string
    {
        $media = $this->getFirstMedia('avatar');

        if (! $media) {
            return null;
        }

        $media->setRelation('model', $this);

        return rescue(fn () => MediaTemporaryUrl::make($media)->getUrl('thumb'));
    }

    protected function avatar(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => $this->thumbnailUrl(),
        )->shouldCache();
    }
}
