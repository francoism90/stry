<?php

declare(strict_types=1);

namespace Domain\Users\Models;

use Database\Factories\UserFactory;
use Domain\Groups\Concerns\HasGroups;
use Domain\Media\Concerns\InteractsWithMedia;
use Domain\Shared\Casts\AsDateTime;
use Domain\Users\Collections\UserCollection;
use Domain\Users\Concerns\InteractsWithCache;
use Domain\Users\Concerns\InteractsWithSubscription;
use Domain\Users\DataObjects\UserSettings;
use Domain\Users\QueryBuilders\UserQueryBuilder;
use Domain\Users\States\UserState;
use Domain\Videos\Concerns\InteractsWithVideos;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\BroadcastsEvents;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Scout\Searchable;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\ModelStates\HasStates;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasMedia, MustVerifyEmail
{
    use BroadcastsEvents;
    use HasApiTokens;
    use HasFactory;
    use HasGroups;
    use HasRoles;
    use HasStates;
    use HasUlids;
    use InteractsWithCache;
    use InteractsWithMedia;
    use InteractsWithSubscription;
    use InteractsWithVideos;
    use Notifiable;
    use Searchable;
    use SoftDeletes;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'state',
    ];

    /**
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'state' => UserState::class,
            'settings' => UserSettings::class.':default',
            'password' => 'hashed',
            'email_verified_at' => AsDateTime::class,
            'created_at' => AsDateTime::class,
            'updated_at' => AsDateTime::class,
            'deleted_at' => AsDateTime::class,
        ];
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    public function newEloquentBuilder($query): UserQueryBuilder
    {
        return new UserQueryBuilder($query);
    }

    public function newCollection(array $models = []): UserCollection
    {
        return new UserCollection($models);
    }

    public function uniqueIds(): array
    {
        return ['ulid'];
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    public function guardName(): array
    {
        return ['api', 'web'];
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

    public static function findFromUlid(User|string $value): ?User
    {
        if ($value instanceof User) {
            return $value;
        }

        return User::query()->firstWhere('ulid', $value);
    }

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(string $event): array
    {
        return [$this];
    }

    public function broadcastChannel(): string
    {
        return 'users.'.$this->getRouteKey();
    }

    public function broadcastAs(string $event): string
    {
        return "user.{$event}";
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

    public function receivesBroadcastNotificationsOn(): string
    {
        return $this->broadcastChannel();
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => (string) $this->getScoutKey(),
            'name' => (string) $this->name,
            'email' => (string) $this->email,
            'state' => (string) $this->state,
            'email_verified_at' => (int) $this->email_verified_at?->getTimestamp(),
            'created_at' => (int) $this->created_at->getTimestamp(),
            'updated_at' => (int) $this->updated_at->getTimestamp(),
        ];
    }

    public function isAdmin(): bool
    {
        return $this->hasAnyRole('admin', 'super-admin');
    }

    public function thumbnailUrl(): ?string
    {
        $media = $this->getFirstMedia('avatar');

        if (! $media) {
            return null;
        }

        $media->setRelation('model', $this);

        return rescue(fn () => $media->getTemporaryUrl(now()->addWeek(), 'thumb'));
    }

    protected function avatar(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => $this->thumbnailUrl(),
        )->shouldCache();
    }

    protected function assignedRoles(): Attribute
    {
        return Attribute::make(
            get: fn (): Collection => $this->getRoleNames(),
        )->shouldCache();
    }

    protected function assignedPermissions(): Attribute
    {
        return Attribute::make(
            get: fn (): Collection => $this->getAllPermissions()->pluck('name'),
        )->shouldCache();
    }

    protected function preferences(): Attribute
    {
        return Attribute::make(
            get: fn (): array => UserSettings::fromModel($this)->include('*')->toArray(),
        )->shouldCache();
    }
}
