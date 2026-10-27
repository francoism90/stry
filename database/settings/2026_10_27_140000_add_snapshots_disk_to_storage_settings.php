<?php

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    /**
     * Adds a "snapshots" disk for database snapshots: a copy of the disk that holds exports, in its
     * "snapshots" folder. An S3 exports disk passes on its credentials, which are kept by disk name.
     */
    public function up(): void
    {
        $exports = null;

        $this->migrator->update('storage.disks', function (array $disks) use (&$exports): array {
            $disks = $this->toArrays($disks);

            if (Collection::make($disks)->contains(fn (array $disk): bool => $disk['name'] === 'snapshots')) {
                return $disks;
            }

            $exports = Collection::make($disks)->first(fn (array $disk): bool => in_array('exports', $disk['default_for'], true));

            if ($exports === null) {
                return $disks;
            }

            $path = rtrim((string) $exports['path'], '/');

            $disks[] = [
                ...$exports,
                'name' => 'snapshots',
                'label' => 'Snapshots',
                'path' => $path === '' ? 'snapshots' : "{$path}/snapshots",
                'contents' => ['snapshots'],
                'default_for' => ['snapshots'],
            ];

            return $disks;
        });

        if ($exports === null) {
            return;
        }

        if ($exports['driver'] === 'local') {
            rescue(fn () => File::ensureDirectoryExists(rtrim((string) $exports['path'], '/').'/snapshots'), report: false);
        }

        $this->copyCredentials($exports['name']);
    }

    /**
     * The migrator casts the disks to StorageDisk objects, which are turned back into plain arrays.
     *
     * @param  array<int, mixed>  $disks
     * @return array<int, array<string, mixed>>
     */
    protected function toArrays(array $disks): array
    {
        return array_map(fn (mixed $disk): array => (array) json_decode((string) json_encode($disk), true), $disks);
    }

    /**
     * The migrator's updateEncrypted() casts the payload before decrypting it, so the encrypted
     * credentials are read and written directly.
     */
    protected function copyCredentials(string $from): void
    {
        $query = DB::table('settings')->where('group', 'storage')->where('name', 'credentials');

        $credentials = json_decode((string) json_encode(decrypt(json_decode((string) $query->value('payload')))), true);

        if (! isset($credentials[$from])) {
            return;
        }

        $query->update(['payload' => json_encode(encrypt([...$credentials, 'snapshots' => $credentials[$from]]))]);
    }
};
