<?php

declare(strict_types=1);

namespace Domain\Playlists\Models;

use Database\Factories\PlaylistFactory;
use Domain\Playlists\Collections\PlaylistCollection;
use Domain\Playlists\Enums\PlaylistType;
use Domain\Playlists\Observers\PlaylistObserver;
use Domain\Playlists\QueryBuilders\PlaylistQueryBuilder;
use Domain\Playlists\States\Failed;
use Domain\Playlists\States\PlaylistState;
use Domain\Playlists\States\Verified;
use Domain\Shared\Casts\AsDateTime;
use Domain\Users\Concerns\InteractsWithUser;
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
use Illuminate\Support\Facades\Config;
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
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'state' => PlaylistState::class,
            'type' => PlaylistType::class,
        ];
    }

    public function newEloquentBuilder($query): PlaylistQueryBuilder
    {
        return new PlaylistQueryBuilder($query);
    }

    public function newCollection(array $models = []): PlaylistCollection
    {
        return new PlaylistCollection($models);
    }

    protected static function newFactory(): PlaylistFactory
    {
        return PlaylistFactory::new();
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
        return static::query()
            ->expired()
            ->orWhere(fn ($query) => $query->failed());
    }

    public static function findFromUlid(Playlist|string $value): ?Playlist
    {
        if ($value instanceof Playlist) {
            return $value;
        }

        return Playlist::query()->firstWhere('ulid', $value);
    }

    /**
     * @return array<int, \Illuminate\Broadcasting\Channel>
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

    public function broadcastQueue(): string
    {
        return 'broadcasts';
    }

    public function broadcastAfterCommit(): bool
    {
        return true;
    }

    public function getModel(): ?Model
    {
        return $this->playlistable;
    }

    public function getDisk(): string
    {
        return $this->disk;
    }

    public function getFileName(): string
    {
        return $this->file_name;
    }

    public function getUrl(): string
    {
        return $this->getUrlResolver($this->file_name);
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

    public function getUrlResolver(?string $path = null): string
    {
        return URL::temporarySignedRoute('api.playlists.playlist', now()->addHour(), [
            'playlist' => $this,
            'path' => $path,
        ]);
    }

    public function getMediaUrlResolver(string $path): string
    {
        $expiration = once(fn () => now()->addHour());

        return $this->getFilesystem()->temporaryUrl($this->getPath($path), $expiration);
    }

    public function getKeyUrlResolver(string $path): string
    {
        return $this->getFilesystem()->temporaryUrl($this->getPath($path), now()->addHour());
    }

    public static function getDestinationDisk(): string
    {
        return Config::string('playlists.disk_name', 'segments');
    }

    public static function getDefaultAudioCodecs(): array
    {
        return Config::array('playlists.audio_codecs', ['aac']);
    }

    public static function getDefaultVideoCodecs(): array
    {
        return Config::array('playlists.video_codecs', ['h264']);
    }

    public static function getResolutions(): array
    {
        return Config::array('playlists.resolutions', ['1080p', '720p', '480p']);
    }

    public static function getSegmentDuration(): int
    {
        return Config::integer('playlists.segment_duration', 6);
    }

    public static function getExpiresAfter(): ?Carbon
    {
        $expires = Config::integer('playlists.expires_after');

        return $expires === 0 ? null : Carbon::now()->addSeconds($expires);
    }

    public static function getEncryptionMethod(): ?string
    {
        return Config::string('playlists.encryption');
    }

    public static function getProtectionScheme(): ?string
    {
        return Config::string('playlists.protection_scheme');
    }

    public static function getKeyRotation(): bool
    {
        return Config::boolean('playlists.key_rotation', false);
    }

    public static function getKeyRotationDuration(): int
    {
        return Config::integer('playlists.key_rotation_duration', 300);
    }

    public static function getPackagerOptions(): array
    {
        return Config::array('playlists.packager_options', []);
    }

    public static function getStreamerOptions(): array
    {
        return Config::array('playlists.streamer_options', []);
    }
}
