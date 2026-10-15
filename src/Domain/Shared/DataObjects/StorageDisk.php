<?php

declare(strict_types=1);

namespace Domain\Shared\DataObjects;

use Domain\Shared\Enums\DiskContent;
use Domain\Shared\Enums\DiskDriver;
use Illuminate\Support\Facades\Config;
use Spatie\LaravelData\Data;

class StorageDisk extends Data
{
    /**
     * @param  array<int, string>  $contents
     * @param  array<int, string>  $default_for
     */
    public function __construct(
        public string $name,
        public string $label,
        public DiskDriver $driver,
        public string $path = '',
        public string $visibility = 'private',
        public bool $serve = false,
        public array $contents = [],
        public array $default_for = [],
        public ?string $bucket = null,
        public ?string $region = null,
        public ?string $endpoint = null,
        public ?string $url = null,
        public ?string $temporary_url = null,
        public ?bool $use_path_style_endpoint = null,
        public ?string $cache_control = null,
    ) {}

    public function contains(DiskContent $content): bool
    {
        return in_array($content->value, $this->contents, true);
    }

    public function isDefaultFor(DiskContent $content): bool
    {
        return in_array($content->value, $this->default_for, true);
    }

    public function isLocal(): bool
    {
        return $this->driver === DiskDriver::Local;
    }

    /**
     * Whether files on the disk can be shown in the browser through temporary URLs.
     */
    public function providesTemporaryUrls(): bool
    {
        return ! $this->isLocal() || $this->serve;
    }

    /**
     * The disk as a "filesystems.disks" entry. S3 disks take the settings of the "s3" disk in
     * config/filesystems.php (the AWS_* variables), unless they set their own.
     *
     * @return array<string, mixed>
     */
    public function toConfig(?StorageCredentials $credentials = null): array
    {
        if ($this->isLocal()) {
            return [
                'driver' => 'local',
                'root' => $this->path,
                'visibility' => $this->visibility,
                'serve' => $this->serve,
                'throw' => true,
                'report' => true,
            ];
        }

        $overrides = array_filter([
            'key' => $credentials?->key,
            'secret' => $credentials?->secret,
            'region' => $this->region,
            'bucket' => $this->bucket,
            'root' => $this->path,
            'url' => $this->url,
            'temporary_url' => $this->temporary_url,
            'endpoint' => $this->endpoint,
            'use_path_style_endpoint' => $this->use_path_style_endpoint,
            'options' => $this->cache_control ? ['CacheControl' => $this->cache_control] : null,
        ], fn (mixed $value): bool => $value !== null && $value !== '');

        return [
            ...self::getDefaultS3Config(),
            ...$overrides,
            'driver' => 's3',
            'visibility' => $this->visibility,
            'throw' => false,
            'report' => true,
        ];
    }

    /**
     * The "s3" disk from config/filesystems.php, which S3 disks inherit from.
     *
     * @return array<string, mixed>
     */
    public static function getDefaultS3Config(): array
    {
        return Config::array('filesystems.disks.s3', []);
    }
}
