# Upgrade Guide

## Moving to plain Quadlet units

**stry** no longer uses [foxws/laravel-podman](https://github.com/foxws/laravel-podman) or [Laravel Installer](https://github.com/foxws/laravel-installer). The Quadlet units are plain files in `containers/quadlets/`, ready to install with [Pods](https://github.com/foxws/pods) or `podman quadlet install`: there's no `php artisan podman:setup`, no `podman/` folder and no `PODMAN_*` keys anymore. The units, containers, volumes and secrets keep their names (`stry`, `systemd-stry-pgsql`, `stry-pgsql`, `stry-env` and so on), so your data and `.env` stay as they are.

### What changes

- **Units.** `containers/stubs/*/quadlets/*.quadlets` became one file per unit in `containers/quadlets/production/` and `containers/quadlets/development/`. The Containerfiles moved to `containers/runtimes/`.
- **Settings in `.env`.** The `PODMAN_*` keys are gone. Your user and group ID come from systemd (`%U` and `%G`). The library folders are the `stry-videos`, `stry-import` and `stry-exports` volumes, which point at `/srv/library/*` by default: if you set `PODMAN_VIDEOS_PATH`, `PODMAN_IMPORT_PATH` or `PODMAN_EXPORTS_PATH`, point them at those folders with a drop-in (see [Another library folder](docs/podman.md#another-library-folder)). For other changes, such as `PODMAN_IMAGE_REGISTRY`, use a drop-in (see [Changing a unit](docs/podman.md#changing-a-unit)).
- **No ports on the host.** Every unit, the app included, uses `ExposeHostPort=`, so the units don't clash with other apps or users. If your reverse proxy pointed at port 8000, publish it again with a drop-in, `~/.config/containers/systemd/stry.container.d/port.conf` with `[Container]` and `PublishPort=8000:8000` (see [Podman Quadlet](docs/podman.md#the-units)).
- **On-demand is optional.** The app now starts at boot. To keep it sleeping when idle, turn it on with `lpod stry ondemand enable` (see [On-demand services](docs/podman.md#on-demand-services)), which writes the units from the settings in `pods.yml`. Its idle check runs `php artisan app:idle` instead of `podman:idle`, and the `laravel-idle@.timer` template is replaced by `stry-idle.timer`.
- **Commands.** `podman:s3-setup` is now `app:s3-setup`. Its buckets are set in `config/filesystems.php` (`buckets` and `cors_buckets`) instead of `PODMAN_S3_BUCKETS` and `PODMAN_S3_CORS_BUCKETS`.
- **Installer.** The `installer/` folder and its guide are gone. Install with Pods, or follow [Production Setup](docs/production.md).
- **Devcontainer.** `containers/devcontainer/devcontainer.json` replaces the generated configs. Point your `.devcontainer/devcontainer.json` symlink at it (see [Development](docs/development.md#vs-code-dev-containers)).
- **Podman 6** is now the minimum.

### Steps

1. **Back up the database and your volumes.** See the [backup example](docs/production.md#security-checklist). Don't use `lpod remove` or `lpod uninstall` for the steps below: they delete the service's volumes.

2. **Turn off the old on-demand units**, if you installed them:

    ```bash
    systemctl --user disable --now stry-ondemand.socket laravel-idle@stry.timer stry-schedule.timer
    rm -r ~/.config/systemd/user/stry-ondemand.* ~/.config/systemd/user/stry-schedule.timer ~/.config/systemd/user/laravel-idle@stry.service.d
    ```

    Also remove `~/.config/systemd/user/laravel-idle@.service` and `laravel-idle@.timer` when no other app on the host uses them.

3. **Remove the old unit files.** List them with `podman quadlet list`, then remove stry's with `podman quadlet rm` (add `--recursive` for units that belong to an application). This removes the Quadlet files only; the Podman volumes, such as `stry-pgsql`, and the secrets stay.

4. **Install the new units**, with Pods (see [Production Setup](docs/production.md#install)) or by hand:

    ```bash
    cd ~/projects/stry && git pull
    podman quadlet install --application=stry containers/quadlets/production

    # Publish the app's port again, if your reverse proxy points at 8000
    mkdir -p ~/.config/containers/systemd/stry.container.d
    printf '[Container]\nPublishPort=8000:8000\n' > ~/.config/containers/systemd/stry.container.d/port.conf
    systemctl --user daemon-reload

    systemctl --user start stry
    ```

    The `stry-videos`, `stry-import` and `stry-exports` volumes are new: they're created on the first start and point at your library, so nothing is copied. If your library isn't in `/srv/library`, add their drop-ins (see [Another library folder](docs/podman.md#another-library-folder)) before that first start, with Pods too.

5. **Remove `PODMAN_*` from your `.env`**, and store it as the `stry-env` secret again (`podman secret create --replace stry-env .env`). The keys are ignored now, so this is only cleanup.

6. Add the [on-demand services](docs/podman.md#on-demand-services) again if you used them.

## Upgrading to 2.0 from 1.x

stry 2.0 plays every video directly from its stored file. Playlists no longer have to be packaged before a video can be watched: the app builds the DASH manifest and HLS playlist when a player asks for them, and cuts the segments as they're requested. This is done by [foxws/laravel-media](https://github.com/foxws/laravel-media), which replaces laravel-ffmpeg, laravel-shaka and laravel-streamer.

Because of this, 2.0 removes the packaged playlists and some stored files that only they used. **Read the whole guide before you start**, and follow the steps in order. The general upgrade steps are in [docs/upgrading.md](docs/upgrading.md); this guide covers what is specific to 2.0.

### What changes

- **Direct play is the only playback mode.** The `playlists` table, the playlist model and the packaged playback modes are gone. The **Admin → Playlist** settings page is now **Admin → Playback**.
- **Seek preview thumbnails replace storyboards.** Stored storyboards are deleted. Regenerate the thumbnails after upgrading (step 6).
- **Chapters are served from the database.** The stored chapter WebVTT files are deleted; the player gets the chapters as markers in the stream.
- **Smaller renditions are encoded while they're watched**, if you turn them on. Renditions that were encoded ahead of time and stored next to a clip are deleted.
- **The `segments` and `secrets` S3 buckets are no longer used.** Segments are cached on the local `/cache` volume, and encryption keys are derived per request from `APP_KEY`, so no keys are stored.
- **AV1 transcoding uses laravel-ab-av1 3.x.** Transcodes work as before, but several of its `.env` keys are gone (see step 2).
- **The `transcoding` queue is merged into `processing`.** A new `media` queue packages the next segments ahead of the player.

### 1. Before you upgrade

**Back up the database.** Some migrations delete stored files, and those files don't come back with a rollback. See [Rolling back](#rolling-back).

**Let the queues run empty.** Jobs on the old `transcoding` queue and jobs for playlists can't run on 2.0. Wait until Horizon (`/horizon`) shows no pending or running jobs, and don't start new transcodes until the upgrade is done.

### 2. Update your `.env`

**Remove these keys.** They belonged to packages that 2.0 no longer uses, and are ignored now:

| Removed                                                                                                 | Replaced by                                                                                                                                          |
| ------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------- |
| `VIDEO_CREATE_PLAYLISTS`                                                                                | Nothing: videos play directly                                                                                                                        |
| `QUEUE_TRANSCODING_MIN_PROCESSES`, `QUEUE_TRANSCODING_MAX_PROCESSES`                                    | Transcodes run on the `processing` supervisor. `QUEUE_MEDIA_MIN_PROCESSES` and `QUEUE_MEDIA_MAX_PROCESSES` set the workers for the new `media` queue |
| `PACKAGER_*` (laravel-shaka)                                                                            | `MEDIA_*`, see [Direct play](docs/configuration.md#direct-play)                                                                                      |
| `STREAMER_*` (laravel-streamer)                                                                         | `MEDIA_*`, and `MEDIA_DELIVERY_HARDWARE` for GPU encoding                                                                                            |
| `FFMPEG_LOG_CHANNEL`, `FFMPEG_TEMPORARY_FILES_ROOT`, `FFMPEG_TEMPORARY_ENCRYPTED_HLS`, `FFMPEG_THREADS` | `MEDIA_LOG_CHANNEL`, `MEDIA_TEMPORARY_FILES_ROOT`                                                                                                    |
| `AB_AV1_LOG_CHANNEL`, `AB_AV1_TEMPORARY_FILES_ROOT`, `AB_AV1_CACHE_FILES_ROOT`                          | `MEDIA_LOG_CHANNEL`, `MEDIA_TEMPORARY_FILES_ROOT`, `MEDIA_CACHE_FILES_ROOT`                                                                          |
| `AB_AV1_FORCE_GENERIC_INPUT`, `AB_AV1_VERBOSITY`, `AB_AV1_VFRAMES`                                      | Nothing                                                                                                                                              |

`FFMPEG_PATH` and `FFPROBE_PATH` still work. The other `AB_AV1_*` keys, such as `AB_AV1_PRESET` and `AB_AV1_MIN_VMAF`, are unchanged.

**Optionally, encode renditions on the GPU** (step 5):

```env
# none, vaapi, nvenc or qsv; a GPU that can't be opened falls back to the CPU
MEDIA_LADDER_HARDWARE=vaapi
# VAAPI driver: radeonsi (AMD), iHD (newer Intel) or i965 (older Intel)
LIBVA_DRIVER_NAME=radeonsi
```

See `.env.example` for the other `MEDIA_*` keys.

Then store the new `.env` as the app's secret again:

```bash
lpod stry secrets
```

### 3. Regenerate the Podman files

The `app` Quadlet unit changed: the app container now gets the GPU (`AddDevice=/dev/dri` and `GroupAdd=keep-groups`), like `stry-horizon` already did, because renditions are encoded while they're watched. Regenerate the Podman files the way you did during setup (see [Generate the Podman files](docs/production.md#generate-the-podman-files)), then reinstall the units:

```bash
podman pull ghcr.io/francoism90/stry:2.0
lpod install production/app.quadlets --replace
lpod install production/horizon.quadlets --replace
# ...and every other service whose image changed...
```

For VAAPI, the host user that runs the containers must be in the `render` and `video` groups. If your server has no `/dev/dri`, remove the two lines from both units, as you did for `stry-horizon` in 1.x.

If you installed the units by hand from the templates, copy the two lines from `containers/stubs/production/quadlets/app.quadlets` into your unit.

### 4. Run the migrations

```bash
lpod stry artisan migrate --force
lpod stry artisan settings:clear-cache
```

The migrations:

- drop the `playlists` table,
- delete the stored storyboards, chapter WebVTT files and pre-encoded clip renditions,
- move the playlist settings to the new playback settings: the subtitle language, encryption and the URL refresh time are kept, and the other playlist settings are removed.

### 5. Check the settings

Open **Admin → Playback** and **Admin → Processing**:

- **Encryption** (Playback) now encrypts every stream per request: AES-128 for HLS and ClearKey for DASH. It's carried over from your playlist settings.
- **Renditions** (Playback) and **Create renditions** (Processing) are new and off by default. Turn them on to offer smaller qualities for slow connections, for example 720 and 480. See [Renditions](docs/configuration.md#renditions).
- **Extract thumbnails** (Processing) replaces the storyboard setting.

### 6. Regenerate the seek preview thumbnails

Existing videos have no seek preview thumbnails until you create them:

```bash
lpod stry artisan videos:thumbnails --force
```

This reads every clip, so it can take a while on a large library. Run it for some videos first by passing their IDs, or only for short clips with `--shorter-than=600`. New videos get their thumbnails when they're imported.

### 7. Clean up the old buckets

The packaged segments and encryption keys of 1.x stay in the `segments` and `secrets` buckets, because the `playlists` migration only drops the table. Once 2.0 runs well, delete those two buckets (or their contents) with your S3 provider's tools. If you set `PODMAN_S3_BUCKETS` or `PODMAN_S3_CORS_BUCKETS` in your `.env`, remove `segments` and `secrets` from them too.

### 8. Check that it worked

- Play a few videos, including one with chapters and one with captions, and try seeking.
- Horizon shows the `processing` and `media` queues working.
- The scheduler runs `media:prune` daily at 03:30 to delete cached segments older than a week. It runs with your existing scheduler; nothing to set up.

### Commands

| 1.x               | 2.0                                                               |
| ----------------- | ----------------------------------------------------------------- |
| `playlists:clear` | Removed                                                           |
| —                 | `videos:thumbnails`: regenerate seek preview thumbnails           |
| —                 | `media:prune`: delete cached segments (runs daily)                |
| —                 | `media:info`: show the FFmpeg, ffprobe and ab-av1 that were found |

### Rolling back

The deleted storyboards, chapter files, pre-encoded renditions and playlist records can't be restored by a migration. To go back to 1.x:

1. Restore the database backup you made in step 1.
2. Pull the previous image (`podman pull ghcr.io/francoism90/stry:1.8`) and reinstall the units.
3. Regenerate the playlists and storyboards in 1.x, because their files were deleted.
