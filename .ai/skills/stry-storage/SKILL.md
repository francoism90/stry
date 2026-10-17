---
name: stry-storage
description: Work with stry's storage disks, which admins manage in the storage settings instead of config/filesystems.php. Covers StorageSettings and StorageDisk, DiskContent and picking the disk for new files, registering disks at runtime with RegisterStorageDisks, the restart notice when workers run with old disks, moving videos between disks and testing storage. Use when adding a disk field, a content type or a storage-related setting, when a disk is missing or stale in Octane or a queue worker, or when the restart banner shows up (or doesn't) after changing storage.
---

# Storage disks in stry

The `media`, `import`, `exports` and `conversions` disks aren't in `config/filesystems.php`. Admins manage them on Admin → Storage, and they're stored in `Foundation\Settings\StorageSettings` (spatie/laravel-settings, group `storage`). They're added to `filesystems.disks` at runtime. Only built-in disks such as `local`, `public`, `cache` and `s3` remain in the config file.

## Where things live

| Piece | Location | Role |
|---|---|---|
| Settings | `src/Foundation/Settings/StorageSettings.php` | `disks` (list of `StorageDisk`), `credentials` (S3 keys by disk name, stored encrypted) |
| Disk | `src/Domain/Shared/DataObjects/StorageDisk.php` | One disk; `toConfig()` turns it into a `filesystems.disks` entry |
| Contents | `src/Domain/Shared/Enums/DiskContent.php` | What a disk holds (videos, captions, conversions, thumbnails, reels, transcodes, import, exports) |
| Registering | `src/Support/Filesystem/RegisterStorageDisks.php` | Writes the disks to config, forgets resolved disks that changed, makes the fingerprint |
| Boot hook | `AppServiceProvider::bootStorageDisks()` | Registers on boot, after migrations and after `StorageSettings` is saved |
| Notices | `src/Support/System/ApplicationNotices.php` | `restart` and `migrations` flags for the admin banner |
| Saving | `Domain\Shared\Actions\UpdateStorageSettings` | Called by `StorageSettingsController`, validated by `StorageSettingsRequest` |
| First migration | `database/settings/2026_10_15_120000_create_storage_settings.php` | Moves the old config disks into the settings, keeping their names |

## Picking a disk

Don't hardcode disk names for new files. Use `DiskContent::X->disk()`. It returns the disk that is `default_for` that content, and falls back to `defaultDisk()` when the settings can't be read (before migrating, or in a fresh install):

```php
$video->addMedia($file)->toMediaCollection('clips', DiskContent::Videos->disk());
```

Existing media keep the disk stored on their record, so renaming or removing a disk breaks those files. A disk's `name` is effectively permanent; its `label` is only for display.

Rules that `StorageSettingsRequest` enforces:

- `AllowedDiskPath`: a local disk's path must be a writable folder that isn't inside, or around, one of its `FORBIDDEN` folders (the app's own, `/tmp` and system folders) or `base_path()`.
- `DiskSupportsContents`: a local disk can only hold conversions, thumbnails or reels (`requiresTemporaryUrls()`) when `serve` is on.
- `CheckDiskConnection`: a disk must be reachable before it's saved.

S3 disks inherit everything from the `s3` disk (the `AWS_*` variables) and override only the fields they set. Credentials are kept by disk name in `credentials`, never in `disks`.

## Workers and the restart notice

Octane and queue workers register the disks once, when they boot. The process that saves the settings registers them again right away. Other workers keep the old disks until they restart.

To show when a restart is needed, `bootStorageDisks()` stores `filesystems.fingerprint` (a hash of `toFilesystemConfig()`) when the process boots. `ApplicationNotices::requiresRestart()` compares that hash with the current one, and the admin banner (`resources/js/components/Ui/AppNotices.vue`, through `ApplicationNoticesProperty`) shows the result.

- Only fields that end up in `StorageDisk::toConfig()` change the fingerprint: path, visibility, serve, bucket, region, endpoint, URLs, path-style, cache control and credentials, plus adding or removing a disk. Changing `label`, `contents` or `default_for` doesn't need a restart: they're read from the settings on each request, so no banner appears.
- When you add a `StorageDisk` field, decide whether workers need it. If they do, put it in `toConfig()` so changing it triggers the banner. If it's display or routing only, keep it out.
- `php artisan app:restart` (or `app:update`) runs `RestartApplication`: `queue:restart`, plus a detached `octane:reload`.

## Moving videos between disks

`Domain\Videos\Jobs\MoveVideo` (queue `processing` on `redis-long`, unique per video, `WithoutOverlapping`) calls `MoveVideoMedia`. Only the `clips` and `captions` collections move. Conversions, thumbnails and reels stay where they are. Each file is streamed to the target, checked by size, then the media's `disk` is updated with `saveQuietly()`, and only after that is the source deleted. Keep that order when you change it, so a failed move never loses the original. The user is notified with `VideoMovedNotification` or `VideoMoveFailedNotification`.

## Testing

See `tests/Feature/Settings/StorageSettingsControllerTest.php` and `tests/Feature/Video/MoveVideoMediaTest.php`:

- Create the local disk folders under `storage_path('framework/testing/disks')` and delete them in `afterEach`.
- Bind `AllowedDiskPath` with its own forbidden folders (the test disks live under `/app`, which is forbidden by default), and mock `CheckDiskConnection` so nothing connects to S3.
- Set disks with `UpdateStorageSettings` or the settings class. The `SettingsSaved` listener registers them, so `Storage::disk($name)` resolves the new config straight away.
- The upgrade path from the latest release is covered by `.github/workflows/upgrade.yml`. It fails when a disk is missing after the upgrade or a notice is still showing.
