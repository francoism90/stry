<?php

declare(strict_types=1);

namespace Domain\Playlists\Models;

use Domain\Playlists\Collections\PlaylistCollection;
use Domain\Playlists\Observers\PlaylistObserver;
use Domain\Playlists\QueryBuilders\PlaylistQueryBuilder;
use Domain\Playlists\States\PlaylistState;
use Domain\Playlists\States\Verified;
use Domain\Users\Concerns\InteractsWithUser;
use FFMpeg\Format\Video\DefaultVideo;
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
use Illuminate\Support\Collection;
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
            'state' => PlaylistState::class,
            'progress' => AsArrayObject::class,
            'accessed_at' => 'datetime',
            'expires_at' => 'datetime',
            'transcoded_at' => 'datetime',
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
            ->orWhere(fn ($query) => $query->failed())
            ->orWhere(fn ($query) => $query->stale());
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

    public function broadcastChannelRoute(): string
    {
        return 'playlists.{playlist}';
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

    public function getSecretDisk(): string
    {
        return $this->secret_disk;
    }

    public function getPath(string $path = ''): string
    {
        return (new WhitespacePathNormalizer)->normalizePath(
            implode('/', [$this->getKey(), $path])
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

    public function getSecretFilesystem(): FilesystemAdapter
    {
        return Storage::disk($this->getSecretDisk());
    }

    public function getMediaUrlResolver(string $path): string
    {
        return $this->getFilesystem()->url($this->getPath($path));
    }

    public function getKeyUrlResolver(string $path): string
    {
        return URL::signedRoute('api.playlists.key', ['playlist' => $this, 'path' => $path]);
    }

    public function getUrlResolver(?string $path = null): string
    {
        return URL::signedRoute('api.playlists.playlist', ['playlist' => $this, 'path' => $path]);
    }

    public function getUrl(): string
    {
        return $this->getUrlResolver($this->file_name);
    }

    public function isValid(): bool
    {
        if (! $this->state->equals(Verified::class)) {
            return false;
        }

        return filled($this->expires_at) ? $this->expires_at->isFuture() : true;
    }

    public static function getVideoFormats(): Collection
    {
        return collect(config('playlist.video_formats', []))
            ->filter(fn (string $format) => is_subclass_of($format, DefaultVideo::class))
            ->map(fn (string $format) => app($format));
    }

    public static function getHlsFormats(): Collection
    {
        return collect(config('playlist.hls_formats', []))
            ->map(fn (array $format) => fluent($format))
            ->sortBy('bit_rate');
    }

    public static function getSegmentLength(): int
    {
        return config('playlist.segment_length', 6);
    }

    public static function getFrameInterval(): int
    {
        return config('playlist.frame_interval', 48);
    }

    public static function getTranscodeDisk(): string
    {
        return config('playlist.disk_name', 'segments');
    }

    public static function getRotationKeyDisk(): string
    {
        return config('playlist.rotation_keys_disk', 'secrets');
    }

    public static function getExpiresAfter(): ?Carbon
    {
        $expires = config('playlist.expires_after');

        return $expires === null ? null : Carbon::now()->addSeconds($expires);
    }

    public static function getStaleAfter(): ?int
    {
        return config('playlist.stale_after');
    }

    public static function getInitialParameters(): array
    {
        return config('playlist.initial_parameters', []);
    }

    public static function getAdditionalParameters(): array
    {
        return config('playlist.additional_parameters', []);
    }

    public static function shouldUseRotationKeys(): bool
    {
        return config('playlist.rotation_keys', true);
    }

    public static function getRotationKeysSections(): int
    {
        return config('playlist.rotation_keys_sections', 5);
    }

    public static function copyVideoCodec(): bool
    {
        return config('playlist.copy_video_codec', true);
    }

    public static function copyAudioCodec(): bool
    {
        return config('playlist.copy_audio_codec', true);
    }
}
