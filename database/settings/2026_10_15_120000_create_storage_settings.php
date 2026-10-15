<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    /**
     * Moves the media, import, exports and conversions disks from config/filesystems.php into the
     * storage settings, keeping their names so existing media keep resolving. The conversions disk
     * only sets its bucket: the rest, including the credentials, comes from the "s3" disk.
     */
    public function up(): void
    {
        $this->migrator->add('storage.disks', [
            $this->localDisk('media', 'Media', '/media', ['videos', 'captions']),
            $this->localDisk('import', 'Import', '/import', ['import']),
            $this->localDisk('exports', 'Exports', '/exports', ['exports']),
            [
                'name' => 'conversions',
                'label' => 'Conversions',
                'driver' => 's3',
                'path' => '',
                'visibility' => 'private',
                'serve' => false,
                'contents' => ['conversions', 'thumbnails', 'reels'],
                'default_for' => ['conversions', 'thumbnails', 'reels'],
                'bucket' => 'conversions',
                'region' => null,
                'endpoint' => null,
                'url' => null,
                'temporary_url' => null,
                'use_path_style_endpoint' => null,
                'cache_control' => 'public, max-age=604800, immutable',
            ],
        ]);

        $this->migrator->addEncrypted('storage.credentials', []);
    }

    /**
     * @param  array<int, string>  $contents
     * @return array<string, mixed>
     */
    protected function localDisk(string $name, string $label, string $path, array $contents): array
    {
        return [
            'name' => $name,
            'label' => $label,
            'driver' => 'local',
            'path' => $path,
            'visibility' => 'private',
            'serve' => false,
            'contents' => $contents,
            'default_for' => $contents,
            'bucket' => null,
            'region' => null,
            'endpoint' => null,
            'url' => null,
            'temporary_url' => null,
            'use_path_style_endpoint' => null,
            'cache_control' => null,
        ];
    }
};
