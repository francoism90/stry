<?php

declare(strict_types=1);

namespace Domain\Playlists\Models;

use Database\Factories\PlaylistFactory;
use Domain\Playlists\Collections\PlaylistCollection;
use Domain\Playlists\Enums\PlaylistType;
use Domain\Playlists\Observers\PlaylistObserver;
use Domain\Playlists\QueryBuilders\PlaylistQueryBuilder;
use Domain\Playlists\Settings\PlaylistSettings;
use Domain\Playlists\States\Failed;
use Domain\Playlists\States\PlaylistState;
use Domain\Playlists\States\Verified;
use Domain\Shared\Casts\AsDateTime;
use Domain\Users\Concerns\InteractsWithUser;
use Illuminate\Broadcasting\Channel;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\BroadcastsEvents;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use League\Flysystem\WhitespacePathNormalizer;
use Spatie\ModelStates\HasStates;

#[ObservedBy(PlaylistObserver::class)]
class Playlist extends Model
{
    use BroadcastsEvents;
    use HasFactory;
    use HasStates;
    use HasUlids;
    use InteractsWithUser;
    use Prunable;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'playlistable_type',
        'playlistable_id',
        'disk',
        'file_name',
        'dash_file_name',
        'hls_file_name',
        'secret_disk',
        'encryption_key_id',
        'encryption_key',
        'progress',
        'type',
        'state',
        'accessed_at',
        'expires_at',
        'transcoded_at',
    ];

    /**
     * @var array<int, string>
     */
    protected $hidden = [
        'user_id',
        'encryption_key_id',
        'encryption_key',
    ];

    /**
     * @var array<string, string>
     */
    protected function casts(): array
    {
        return [
            'progress' => AsArrayObject::class,
            'encryption_key' => 'encrypted',
            'accessed_at' => AsDateTime::class,
            'expires_at' => AsDateTime::class,
            'transcoded_at' => AsDateTime::class,
            'created_at' => AsDateTime::class,
            'updated_at' => AsDateTime::class,
            'state' => PlaylistState::class,
            'type' => PlaylistType::class,
        ];
    }

    protected static function newFactory(): PlaylistFactory
    {
        return PlaylistFactory::new();
    }

    public function newEloquentBuilder($query): PlaylistQueryBuilder
    {
        return new PlaylistQueryBuilder($query);
    }

    public function newCollection(array $models = []): PlaylistCollection
    {
        return new PlaylistCollection($models);
    }

    public function uniqueIds(): array
    {
        return ['ulid'];
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    public function playlistable(): MorphTo
    {
        return $this->morphTo();
    }

    public function prunable(): PlaylistQueryBuilder
    {
        return static::query()->prunable();
    }

    public static function findFromUlid(Playlist|string $value): ?Playlist
    {
        if ($value instanceof Playlist) {
            return $value;
        }

        return Playlist::query()->firstWhere('ulid', $value);
    }

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(string $event): array
    {
        return array_filter([$this, $this->getModel()]);
    }

    public function broadcastChannel(): string
    {
        return 'playlists.'.$this->getRouteKey();
    }

    public function broadcastAs(string $event): string
    {
        return "playlist.{$event}";
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

    public function getModel(): ?Model
    {
        return $this->playlistable;
    }

    public function getUrl(): string
    {
        return $this->getDashUrl();
    }

    public function getDashUrl(): string
    {
        return $this->getUrlResolver($this->getDashFileName() ?? 'index.mpd');
    }

    public function getHlsUrl(): ?string
    {
        return filled($this->hls_file_name) ? $this->getUrlResolver($this->hls_file_name) : null;
    }

    public function getUrlRefreshIn(): int
    {
        $settings = app(PlaylistSettings::class);

        return max($settings->manifest_url_lifetime - $settings->manifest_refresh_before, 0);
    }

    public function markAsReady(): void
    {
        $this->updateOrFail([
            'state' => Verified::class,
            'transcoded_at' => Carbon::now(),
        ]);
    }

    public function markAsFailed(): void
    {
        $this->updateOrFail([
            'state' => Failed::class,
            'transcoded_at' => Carbon::now(),
        ]);
    }

    public function isExpired(): bool
    {
        return filled($this->expires_at) && Carbon::parse($this->expires_at)->isPast();
    }

    public function isFailed(): bool
    {
        return $this->state->equals(Failed::class);
    }

    public function isValid(): bool
    {
        return $this->state->equals(Verified::class);
    }

    public function getDisk(): string
    {
        return $this->disk ?? app(PlaylistSettings::class)->disk_name;
    }

    public function getDashFileName(): ?string
    {
        // Older playlists were only ever assigned a "file_name", so fall back to it
        // for rows written before the dedicated dash/hls filename columns existed.
        return $this->dash_file_name ?? $this->file_name;
    }

    public function getHlsFileName(): ?string
    {
        return $this->hls_file_name;
    }

    public function getType(): PlaylistType
    {
        return $this->type ?? app(PlaylistSettings::class)->type;
    }

    public function getPath(string $path = ''): string
    {
        return (new WhitespacePathNormalizer)->normalizePath(
            implode('/', [$this->getKey(), $path]),
        );
    }

    public function getAbsolutePath(): string
    {
        return $this->getFilesystem()->path($this->getPath());
    }

    public function getFilesystem(): FilesystemAdapter
    {
        return Storage::disk($this->getDisk());
    }

    public function getUrlResolver(string $path): string
    {
        $expiration = now()->addSeconds(app(PlaylistSettings::class)->manifest_url_lifetime);

        return URL::temporarySignedRoute('api.play.manifest', $expiration, [
            'playlist' => $this,
            'path' => $path,
        ]);
    }

    public function getMediaUrlResolver(string $path): string
    {
        $expiration = now()->addSeconds(app(PlaylistSettings::class)->media_url_lifetime);

        return $this->getFilesystem()->temporaryUrl($this->getPath($path), $expiration);
    }

    public function getKeyUrlResolver(string $path): string
    {
        $expiration = now()->addSeconds(app(PlaylistSettings::class)->key_url_lifetime);

        return $this->getFilesystem()->temporaryUrl($this->getPath($path), $expiration);
    }
}
