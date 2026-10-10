---
name: laravel-media-development
description: Probe, process and package audio and video with foxws/laravel-media (ffprobe and ffmpeg), including typed stream and chapter info, upload validation, progress reporting and cancelling, export events, clips, frames, subtitle extraction, scene detection, clip reels and concatenation, several outputs in one run, seek-preview thumbnail sprites with WebVTT, filters (scale, crop, fade, loudnorm, watermark, HDR to SDR tone mapping), encoding presets with bitrate and two-pass control, rendition ladders with hardware encoding, audio-only output, packaging into HLS and DASH with ClearKey encryption, signed manifests through DynamicHLSPlaylist and DynamicDASHManifest, streaming HLS and DASH straight from stored files (nginx-vod-module style) with per-request AES-128 or ClearKey (CENC) encryption, subtitles, thumbnail tracks and chapter or scene markers, and exporting to local or S3 disks. Use when working with the Media facade, Foxws\Media classes, config/media.php, or replacing pbmedia/laravel-ffmpeg and php-ffmpeg.
license: MIT
metadata:
  author: foxws
---

# Media with laravel-media

`foxws/laravel-media` runs ffprobe and ffmpeg on files from any Laravel disk. It builds commands directly. There is no php-ffmpeg underneath, so new ffmpeg options never wait on a package release.

## Opening and probing

<!-- Opening and probing -->
```php
use Foxws\Media\Facades\Media;

$media = Media::fromDisk('s3')->open('videos/clip.mp4');   // or Media::open(...) for the default disk

$probe = $media->probe();

$probe->duration();          // float, seconds
$probe->hasVideo();          // ignores cover art (attached pictures)
$probe->videoStream()?->height;
$probe->videoStream()?->frameRate;
$probe->audioStreams();      // list<AudioStream> (channels, sampleRate, language)
$probe->subtitleStreams();   // list<SubtitleStream> (language, forced())
$probe->chapters();          // list<Chapter> (title, start, end)
$probe->format()->bitRate;
$probe->stream(2)?->get('tags.title');   // any raw ffprobe field via dot notation
```

- `open()` accepts several paths. `probe($path)` probes one (the first by default), and `probeAll()` returns them keyed by path. Results are cached on the opener; after `rememberProbes()` they're also kept in `media.delivery.cache_store` per file version, like keyframe indexes. `stream()` turns that on, so playlist and segment requests don't run ffprobe again.
- On disks that provide temporary URLs (S3), ffprobe and ffmpeg read a short-lived signed URL instead of downloading the file. Set `media.remote_inputs.enabled` to false to download to the temporary root instead.
- FFmpeg 9 verifies TLS certificates of those URLs. For storage behind a private CA, set `media.remote_inputs.ca_file`; `media.remote_inputs.verify_tls` false accepts any certificate (development only). When building your own ffmpeg or ffprobe command around `$media->inputPath()`, put `...$media->inputArguments()` before it; they're empty for local paths, where ffmpeg would reject them.

## Running ffmpeg

<!-- Running ffmpeg -->
```php
use Foxws\Media\Encoding\Format;

$result = Media::fromDisk('s3')
    ->open('videos/clip.mp4')
    ->ffmpeg()
    ->clip(from: 12.5, to: 40)
    ->inFormat(Format::h264(crf: 22))
    ->toDisk('clips')                // defaults to the source disk
    ->withVisibility('private')
    ->afterSaving(fn ($builder, $result) => $clip->markAsReady($result->path()))
    ->save('intro/clip.mp4');

$result->disk();   // target Disk
$result->paths();  // every written path
```

- `frame(at: 5.0)->save('thumb.jpg')` grabs one frame as JPEG.
- `map('0:2')->inFormat(Format::webVtt())->save('captions/nld.vtt')` extracts a subtitle stream.
- `addArgs([...])` adds output options (for example `['-vf', 'scale=1280:-2']`), and `addInputArgs([...])` adds options placed before every input.
- `clip()` seeks on the input, so it applies to every output. With `Format::copy()` the clip starts at the keyframe before `from`.
- `beforeSaving(fn ($builder) => ...)` can still change the command. `afterSaving(fn ($builder, $result) => ...)` runs once, only after the files are on the target disk.
- `command('out.mp4')` returns the full command line with keys redacted, without running it.
- Temporary files, including downloaded remote inputs, are deleted after every queue job and at the end of each request. Call `$media->cleanupTemporaryFiles()` to free them earlier, for example between steps of a long job.

## Keyframes and segments

`keyframes()` lists where a video's keyframes are, by reading its packets with ffprobe (no decoding, so it's fast even for long files). Segments cut on keyframes can be copied without re-encoding, which is what streaming straight from the stored file relies on.

<!-- Keyframes -->
```php
$index = Media::fromDisk('s3')->open('videos/movie.mp4')->keyframes();

$index->keyframes;          // [0.0, 2.002, 4.004, ...] seconds
$index->segments(6);        // list<Segment> (index, start, duration, end()), each at least 6s and starting on a keyframe
$index->longestSegment(6);  // the HLS target duration
```

- Indexes are cached on the opener and in `media.delivery.cache_store` (null for the default store) for `media.delivery.index_lifetime` seconds, keyed by disk, path, size and modification time, so a changed file is indexed again.
- Audio-only files have no keyframes and are split into even segments.
- `media.delivery.segment_duration` (`MEDIA_DELIVERY_SEGMENT_DURATION`, 6) is the default target length.

## Streaming straight from stored files

`stream()` serves the opened files as HLS or DASH without packaging them first, like nginx-vod-module: playlists come from the keyframe index, and each segment is copied into MPEG-TS (or fragmented MP4 per track) the first time it's requested, then kept on a cache disk. Store each video once, as H.264/HEVC with AAC/MP3/AC-3. Every opened file is one variant, for example renditions of one video.

<!-- Direct stream routes -->
```php
use Foxws\Media\Facades\MediaStream;

// AppServiceProvider::boot(): how a stream is resolved from its route parameters
MediaStream::define('videos', function (Video $video) {
    Gate::authorize('view', $video);

    return Media::fromDisk('videos')->open($video->renditions());   // or ->stream()->segmentDuration(4)
})->signed();   // optional: verify signatures and sign every URL in the playlists

// routes/web.php: cmaf.m3u8, hls.m3u8 and dash.mpd, with the playlists, segments and keys they link to
Route::middleware('auth')->group(fn () => Route::mediaStream('videos/{video}', 'videos'));

// the URL to give the player (signed when the stream is)
MediaStream::url('videos', ['video' => $video]);       // CMAF: HLS with fragmented MP4, the segments DASH uses too
MediaStream::dashUrl('videos', ['video' => $video]);   // DASH
MediaStream::hlsUrl('videos', ['video' => $video]);    // HLS with MPEG-TS, e.g. for rotating keys or old devices
MediaStream::chaptersUrl('videos', ['video' => $video]);   // the chapters as WebVTT, for the player's seek bar
```

- **Resolvers:** parameters typed as a model (any `UrlRoutable`) are bound like implicit route model binding (404 when missing); other parameters are injected by the container. Return an `Opener` or a configured `DirectStream`. Authorize inside the resolver or with route middleware.
- **Routes:** `Route::mediaStream($uri, $name)` names its routes `media.{name}.cmaf`, `.hls`, `.dash`, `.playlist`, `.segment`, `.key`, `.license`, `.track-playlist`, `.init`, `.fragment` and `.chapters` (tracks are `video`, `audio`, `audio-{n}` and `iframes`), and works inside `Route::name()`/`prefix()` groups. Playlists link to each other with absolute URLs, and are sent with `private, no-cache`.
- **Signed streams:** `signed($lifetime)` rejects requests without a valid signature (403) and signs every playlist, segment and key URL for `$lifetime` seconds (default `media.delivery.url_lifetime`).

For full control, call the stream yourself from your own routes:

<!-- Direct HLS controller -->
```php
public function variant(Video $video, int $variant): Response
{
    $playlist = $video->streamable()->stream()   // Media::fromDisk(...)->open(['1080.mp4', '720.mp4'])
        ->mediaPlaylist($variant, fn (Segment $segment, int $variant) => URL::temporarySignedRoute('videos.segment', now()->addHours(4), [$video, $variant, $segment->index]));

    return response($playlist, 200, ['Content-Type' => 'application/vnd.apple.mpegurl']);
}

public function segment(Video $video, int $variant, int $segment): Response
{
    return $video->streamable()->stream()->segmentResponse($variant, $segment);   // packages it on the first request
}
```

- `masterPlaylist(fn (int $variant) => ...)` lists the variants with their bandwidth, resolution, frame rate and codecs.
- **Look-ahead:** after a segment is requested, the next `media.delivery.look_ahead` segments (2 by default) that aren't cached yet are packaged ahead of their requests. Playlist and DASH manifest requests do the same for the first segments.
  - `look_ahead_via` `"queue"` (the default) dispatches a `Foxws\Media\Delivery\PackageSegments` job on `look_ahead_connection`/`look_ahead_queue`, e.g. a `media` queue on Horizon. `"defer"` packages them in the same process after the response, and `null` turns look-ahead off.
  - Streams fall back to `defer` on a `sync` queue, or when the media or cache disk isn't a configured disk name that a job could open.
  - Per stream: `->lookAhead(4)`, `->lookAhead(1, LookAheadStrategy::Defer)` or `->lookAhead(0)`. Without the route macro, call `packageAhead($variant, 0, $track)` after serving a playlist and `packageStart()` after a DASH manifest; `segmentResponse()` and `initSegmentResponse()` look ahead by themselves.
  - The per-segment lock still applies, so a segment being packaged ahead is never packaged twice; a request for it waits for the running package.
- **Probes:** each file is probed once per version, then read from `media.delivery.cache_store` for `media.delivery.index_lifetime` seconds, so playlist, init and segment requests skip ffprobe.
- **Segments:** `ffmpeg -ss … -t … -copyts -c copy -f mpegts` copies each segment exactly, because segments start on keyframes. S3 sources are read through signed URLs with range requests, so only the needed bytes are fetched. Concurrent requests for the same segment package it once (`Cache::lock`).
- **The segment cache:** `media.delivery.cache_disk` can be local storage, a mounted `/tmp` or RAM disk, or S3 (override per stream with `toCache()`). Segments are keyed by file version, so a changed file gets new segments. On disks with temporary URLs, `segmentResponse()` redirects to one valid for `media.delivery.url_lifetime` seconds; otherwise it returns the file with long cache headers.
- **Errors:** out-of-range segments and variants throw `SegmentNotFoundException` (a 404). Files with codecs MPEG-TS can't carry (VP9, AV1, Opus, ...) throw `InvalidMediaException`.
- **Formats:** each route picks its own segment format, so one stream definition serves all three URLs. Prefer `url()` (CMAF): one set of cached fragments serves HLS and DASH, and every player plays it natively. MPEG-TS (`hlsUrl()`) is for rotating encryption keys and old devices; Shaka Player needs mux.js loaded (`window.muxjs`) to play it.
- `segmentDuration()` overrides `media.delivery.segment_duration` per stream.
- **Cache layout:** segments are stored at `{cache_path}/{version key}/{segment duration}/{track}/{n}.m4s` (`{n}.ts` for MPEG-TS), with one `init.mp4` per track that's written with the first fragment and kept. The version key hashes the disk, path, size and modification time of the source, so every request, viewer and look-ahead job for the same file reuses the same segments, CMAF HLS and DASH share them, and a replaced file gets a new directory. `php artisan media:prune --older-than=0` empties the segment cache, e.g. to test packaging again.
- **Pruning:** segments are packaged again when requested, so the cache can be pruned at any time. Schedule `php artisan media:prune` daily; it deletes segments (`.ts`, `.m4s` and `init.mp4`) and converted subtitles (`.vtt`) packaged more than `--older-than` minutes ago (default a week) from the cache disk. `--dry-run` counts them.

### Fragmented MP4 and DASH

- **Tracks:** fragmented segments hold one track (`Track::Video` or `Track::Audio`), as CMAF and DASH expect. Every file with video is a video representation, and the audio of the first file with audio is shared by all of them (HLS `#EXT-X-MEDIA` audio rendition, DASH audio adaptation set).
- **Audio languages:** every audio stream of that file is an audio track players pick by language (Shaka Player's `preferredAudioLanguage`): one `#EXT-X-MEDIA` per stream with `NAME` (the stream's title, language or number) and `LANGUAGE`, and one DASH adaptation set per stream with `lang`, `<Label>` and a `main`/`alternate` role. The stream marked as default (or the first) is `DEFAULT=YES`. The first stream is the track `audio`, the others `audio-1`, `audio-2` and so on (`Track::Audio->name($position)`, mapped as `0:a:{position}`), each with its own segments. Streams fragmented MP4 can't carry, like TrueHD or DTS, are left out. `->withAudioStreams(['eng', 'jpn'])` (languages or positions, e.g. `[0]`) limits them. MPEG-TS segments (`hlsUrl()`) carry the first audio stream only. In tests, `FakeProbe::video(audioLanguages: ['eng', 'jpn'])` fakes a file with several.
- **HLS:** CMAF playlists are version 7 with `#EXT-X-MAP` initialization segments. Without routes, `fragmented()` switches `masterPlaylist()` and `mediaPlaylist()` from MPEG-TS to fragmented MP4.
- **Players:** Shaka Player, hls.js, dash.js and Safari play fragmented MP4 natively, without mux.js.
- **Codecs:** fragmented MP4 also carries AV1, VP9, Opus and FLAC. HEVC is tagged `hvc1` for Safari. AV1 gets its `av01` codec string from the probed profile, level and pixel format; VP9 has none, so players probe it themselves.
- **DASH manifests** are static, with a `SegmentList` and millisecond `SegmentTimeline` per representation, so every segment URL can be signed. Fragments keep their source timestamps plus a fixed 10-second offset, which `presentationTimeOffset` removes again.
- **Without routes:** `dashManifest($initUrl, $segmentUrl)`, `mediaPlaylist($variant, $segmentUrl, Track::Video, $initUrl)`, `initSegmentResponse($variant, $track)` and `segmentResponse($variant, $index, $track)`.
- Encrypted streams use Common Encryption in fragmented MP4 and DASH; see below.
- **Renditions on request:** `->withRenditions(new Ladder([new Rendition(720, 2800), new Rendition(480, 1400)]))` adds H.264 variants below the source (only sizes smaller than it), numbered after the opened files. Each segment is encoded when first requested (`-ss start -t duration -copyts`, scaled, one keyframe at its start via `keyframesAt()`, High profile at level 4.2/5.1/5.2 by frame size so codec strings are known up front), split like copied fragments and cached under `renditions/{height}p-{bitrate}-{hash}/video/`. They share one init segment per rendition. Fragmented streams only, video track only, no trick play; look-ahead jobs (`PackageSegments`) carry the ladder. `renditions()` lists them. Non-H.264 ladders throw.
- **Trick play:** `->withTrickPlay()` adds an `#EXT-X-I-FRAME-STREAM-INF` I-frame playlist per video variant to the CMAF master playlist (`#EXT-X-I-FRAMES-ONLY`), and a trick mode adaptation set (`http://dashif.org/guidelines/trickmode`, `codingDependency="false"`) to the DASH manifest, so players like Shaka Player show frames while fast-forwarding. Its segments are `Track::IFrames`: only the keyframe each video segment starts with (`-frames:v 1`), at `{variant}/iframes/...`, cached and encrypted like other tracks. There's one I-frame per segment, so shorter segments give smoother trick play; the bandwidth is estimated as a tenth of the variant's. MPEG-TS playlists (`hlsUrl()`) don't list I-frames.

### Subtitles

<!-- WebVTT subtitles in direct streams -->
```php
MediaStream::define('videos', fn (Video $video) => Media::fromDisk('videos')->open($video->renditions())->stream()
    ->withSubtitles('captions/1_en.vtt', language: 'en', label: 'English')   // a WebVTT file, on the media disk or ->withSubtitles(..., disk: 's3')
    ->withEmbeddedSubtitles());                                               // plus the text subtitle streams of the first file
```

- **HLS:** every track is an `#EXT-X-MEDIA:TYPE=SUBTITLES` rendition with a one-segment playlist, in both `cmaf.m3u8` and `hls.m3u8`. Each format gets its own `.vtt` URL with an `X-TIMESTAMP-MAP` header that lines up the cues with that format's segment timestamps.
- **DASH:** every track is a `text/vtt` adaptation set with `lang`, a `Label` and the subtitle role, pointing straight at the `.vtt` file without `SegmentBase`. Shaka Player and dash.js load it as a single segment (a `SegmentBase` without an index range is what makes Shaka drop sidecar WebVTT).
- **Embedded streams:** SubRip, MP4 text, ASS/SSA and WebVTT streams are converted with `-c:s webvtt` once and cached; bitmap subtitles (PGS, DVD) are skipped. Their label is the stream's title or language.
- **Without routes:** `masterPlaylist($playlistUrl, $subtitleUrl)`, `dashManifest($initUrl, $segmentUrl, $subtitleUrl)`, `subtitlePlaylist($subtitle, $vttUrl)` and `subtitleResponse($subtitle, $timestampOffset)`: pass `0` for MPEG-TS, `FragmentedMp4::TIMESTAMP_OFFSET` for CMAF and `null` for DASH.

### Thumbnails

<!-- Seek previews from the manifest -->
```php
use Foxws\Media\FFMpeg\ThumbnailsResult;

// in the job that stores the video: sampling a whole video is too slow for a request
$result = Media::fromDisk('videos')->open($path)->thumbnails()->every(5)->grid(10, 10)->toDisk('storyboards')->save("{$video->id}/storyboard");
$video->update(['thumbnails' => $result->toArray()]);

// in the stream definition
->stream()->withThumbnails(ThumbnailsResult::fromArray($video->thumbnails))
```

- **HLS:** an `#EXT-X-IMAGE-STREAM-INF` image stream (sheet resolution, `CODECS="jpeg"` or `"webp"`) in both `cmaf.m3u8` and `hls.m3u8`, with an image playlist (`#EXT-X-IMAGES-ONLY`) that has an `#EXT-X-TILES` grid per sheet.
- **DASH:** an `image` adaptation set with the DASH-IF `thumbnail_tile` property (e.g. `10x10`) and every sheet in a `SegmentList`, so sheet URLs can be signed.
- **Players:** Shaka Player reads both through `getImageTracks()` and `getThumbnails($trackId, $time)`, so the player no longer needs `addThumbnailsTrack()` with the WebVTT file. The WebVTT file still works for players that only read that.
- **Serving:** sheets come from the result's disk (`fromArray($data, $disk)` overrides it), as a redirect to a temporary URL on disks that provide them. Routes: `thumbnails.m3u8` and `thumbnails/{sheet}.{jpg|webp}`.
- `ThumbnailsResult` records the grid (`columns`, `rows`) and the tile size (`width`, `height`); `toArray()`/`fromArray()` store it in a JSON column.

### Chapters, scenes and markers

<!-- Named time ranges in the manifest -->
```php
use Foxws\Media\Delivery\Marker;
use Foxws\Media\FFMpeg\Scene;

// in the job that stores the video: scene detection decodes the whole video
$video->update(['scenes' => array_map(fn (Scene $scene) => $scene->toArray(), $media->scenes())]);

// in the stream definition
->stream()
    ->withChapters()                                                   // chapters of the first opened file, class "chapter"
    ->withScenes(array_map(Scene::fromArray(...), $video->scenes))     // class "scene"
    ->withMarkers([new Marker(12.4, 20.6, 'Intro', class: 'intro')])   // leave out the end for a single moment
```

- **HLS:** every video and audio media playlist gets `#EXT-X-PROGRAM-DATE-TIME:1970-01-01T00:00:00.000Z`, so a marker's `START-DATE` is the epoch plus its start in seconds, and one `#EXT-X-DATERANGE` per marker with `ID="{class}-{n}"`, `CLASS`, `DURATION` and the title as `X-TITLE`. Subtitle and image playlists get the same anchor. Without markers, no date tags are added.
- **DASH:** one `<EventStream schemeIdUri="urn:foxws:media:marker" value="{class}" timescale="1000">` per class at the start of the Period, with `<Event id presentationTime duration>` in milliseconds and the title as its text.
- **Players:** Shaka Player fires `timelineregionadded` for DASH events, and for HLS date ranges since 5.2 (older versions fire `metadata` with type `com.apple.quicktime.HLS`). Read `event.detail.schemeIdUri`/`value` (DASH) or the `CLASS` (HLS) to tell the kinds apart.
- `markers()` returns them all, chapters included, sorted by start. `Marker` refuses a negative start, an end before its start and an empty class.
- **Chapter track:** `chapters.vtt` (`MediaStream::chaptersUrl()`) serves the markers of class `chapter` as WebVTT cues with ids `{class}-{n}`, for seek bars, e.g. Shaka Player's `player.addChaptersTrack($url, 'en')` after `load()`. Each cue ends where the next one starts, and one without an end runs to the end of the stream; it's a 404 without chapters. `->chapterTrackFrom(['chapter', 'intro'], 'Main')` lists other classes (`null` for every marker) and fills the gaps between them and after the last one with a cue titled `Main`, so players don't show the last chapter's title until the end. `chapterTrack()->toWebVtt()` returns it without a route.

### Encrypting direct streams

<!-- Per-request AES-128 -->
```php
use Foxws\Media\Encryption\EncryptionKey;

MediaStream::define('videos', fn (Video $video) => Media::fromDisk('videos')->open($video->renditions())->stream()
    ->withEncryption(
        fn (int $period) => EncryptionKey::derive(config('app.key'), "video:{$video->id}:{$period}"),
        rotateEvery: 100,   // a new key every 100 segments; leave out for one key per playlist
    ));
```

- **Key URLs:** `Route::mediaStream()` links and serves the keys itself. Without it, pass the key URL as the second argument of `withEncryption()` (or call `keyUrlsUsing()`), as `fn (int $period, int $variant) => ...`, and return `$stream->keyResponse($period)` from your key route after authorizing the viewer.
- **How it works:** media playlists get `#EXT-X-KEY:METHOD=AES-128` tags, a new one for each rotation period. `segmentResponse()` encrypts each segment for the request with AES-128-CBC, using its period's key and its media sequence number as the IV (the HLS default, so playlists leave the IV out).
- **What's cached:** segments stay unencrypted on the cache disk and are shared by every key. Keep that disk private. Encrypted streams are served by the app instead of redirecting to the cache disk.
- **Keys:** `EncryptionKey::derive($secret, $context)` makes keys deterministic per context (HMAC-SHA256), so they don't need storing. Pass a fixed `EncryptionKey` instead of a callback for one stored key.
- **Serving keys:** `keyResponse($period)` returns the raw 16-byte key with `no-store`, and `key($period)` returns the `EncryptionKey`. Always authorize the key route; with `Route::mediaStream()` the resolver and route middleware run for key requests too.

#### CMAF and DASH (ClearKey)

The same `withEncryption()` stream also serves CMAF and DASH, encrypted with Common Encryption (the `cenc` scheme, AES-128-CTR) as each fragment is requested:

- **One key:** fragmented streams name their key in the initialization segment, so they take one key. A stream with `rotateEvery` throws for CMAF and DASH output; rotate keys over `hlsUrl()` only.
- **HLS:** CMAF track playlists get `#EXT-X-KEY:METHOD=SAMPLE-AES-CTR,KEYFORMAT="identity"` pointing at the key URL. Shaka Player fetches the raw key, reads the key ID from the initialization segment and plays it with ClearKey.
- **DASH:** each adaptation set lists `cenc:default_KID` and a ClearKey `ContentProtection` with a `dashif:Laurl` license URL. `Route::mediaStream()` serves the license as `license.json` (GET or POST); without it, call `licenseUrlUsing(fn () => ...)` and return `$stream->licenseResponse()` (a JSON Web Key Set) from your own route.
- **CSRF:** players POST license requests, so register the streams where CSRF protection doesn't apply (e.g. `routes/api.php`) or exclude `*/license.json` from it.
- **Fragments:** audio samples are encrypted whole. H.264 and HEVC samples are encrypted per NAL unit, so lengths, NAL headers and parameter sets stay readable. AV1 samples are encrypted per tile, as the AV1 ISOBMFF binding requires: OBU headers, sequence and frame headers and tile sizes stay readable, and the whole blocks at the end of each tile are encrypted. Initialization segments get `encv`/`enca` sample entries with the scheme and key ID, plus a Common PSSH box. Each sample gets its own 8-byte IV, derived from the variant, track, segment and sample number.
- **Codecs:** VP9 can't be encrypted yet (its frame headers would have to be parsed), so encrypted CMAF and DASH output of it throws `InvalidMediaException`. AV1 frame headers are read to find the tiles; a bitstream that can't be read throws `InvalidMediaException` too.
- **Players:** browsers support ClearKey through EME (Chrome, Edge and Firefox). Safari doesn't, so give it `hlsUrl()`. ClearKey hands the key to the browser, so it protects segments at rest and in transit, not from viewers.
- `EncryptionKey::keyIdUuid()` formats the key ID as a UUID, and `toJsonWebKey()` as ClearKey's JSON Web Key.

### Playable files

Direct streams copy streams as they are, so browsers must decode the source codecs. Check and repair at import, in a queued job:

<!-- Making a file playable -->
```php
$media = Media::fromDisk('videos')->open('movie.mkv');

if (! $media->playability()->isPlayable()) {
    $media->makePlayable('movie-playable.mkv')->toDisk('videos')->save();   // then replace the source with it
}
```

- Playable codecs come from `media.playback.video_codecs` (`hevc,h264,av1,vp9`) and `.audio_codecs` (`aac,mp3,opus,flac`); H.264 must be 8-bit 4:2:0, the others 4:2:0 at 8 or 10 bits. Drop `hevc` when Firefox must play everything (its HEVC support is experimental, Nightly only).
- `makePlayable()` copies what plays and re-encodes only the rest: video with `media.playback.video_codec` (`libx264` by default, `libx265`, `libsvtav1`), each unplayable audio stream as AAC (`-c:a:N`). Audio-only repairs copy the video and are fast. MKV outputs keep subtitles; MP4/MOV outputs drop them.
- `needsVideoEncoding()` and `audioNeedingEncoding()` (audio stream positions) tell what's wrong.
- `media.playback.hardware` (`MEDIA_PLAYBACK_HARDWARE`: null/`none` = CPU, `vaapi`, `nvenc`, `qsv`) encodes the repaired video on the GPU. Decoding stays on the CPU (`format=nv12,hwupload` for VAAPI/QSV), and the CRF maps to `-qp` (VAAPI, CQP), `-cq` (NVENC) or `-global_quality` (QSV). `HardwareAcceleration::uploadArguments()`, `upload()` and `quality()` give those arguments.
- The source is never changed, so it can be kept untouched. When only the audio doesn't play, `makePlayable('movie-audio.m4a', audioOnly: true)` writes just the audio streams (playable ones copied, others as AAC); stream it next to the source with `open(['movie.mkv', 'movie-audio.m4a'])->stream()->tracksFrom([0], 1)`, video from variant 0 and audio tracks from variant 1. Audio only throws `InvalidMediaException` when the video doesn't play either.

## Scenes, clips and reels

<!-- A reel from scenes -->
```php
use Foxws\Media\FFMpeg\Clip;
use Foxws\Media\FFMpeg\Scene;

$media = Media::fromDisk('s3')->open(['videos/a.mp4', 'videos/b.mp4']);

$scenes = $media->scenes(threshold: 0.3);           // list<Scene> (start, end, score), cached per threshold
// $scene->toArray() and Scene::fromArray() store scenes in a JSON column
$clips = collect($scenes)
    ->sortByDesc('score')
    ->take(5)
    ->sortBy('start')
    ->map(fn (Scene $scene) => $scene->toClip(maximumDuration: 4))
    ->push(Clip::make(10, 14, 'videos/b.mp4'))      // clips can come from any opened file
    ->values()
    ->all();

$media->ffmpeg()
    ->clips($clips, width: 1080, height: 1920, fps: 30)   // vertical reel; clips are letterboxed to fit
    ->addFilter(Fade::in(0.5), new Loudnorm)              // filters apply to the joined video
    ->inFormat(Format::h264())
    ->save('reels/1.mp4');
```

- **`clips()` re-encodes.** Each clip is a separate input seeked with `-ss`/`-t`, so cuts are frame-accurate. Without a size, clips from several files are fitted to the first file's size. If any file has no audio, the reel is silent.
- **`clips()` reels work with `hardware()`:** the joined and filtered video is uploaded to the GPU and encoded there.
- **`clips()` builds its own inputs and graph,** so it can't be combined with `map()`, `watermark()`, `addOutput()`, `clip()`, `frame()` or `addInputArgs()` (`InvalidFilterException`).
- **`concat()` joins whole files without re-encoding,** using ffmpeg's concat demuxer and copying streams unless a format is set. The files must share codecs, dimensions and audio layout. Otherwise it throws `InvalidMediaException` and you should use `clips()`.
- **`clip($from, $to)` on a single file** with `Format::copy()` starts at the keyframe before `$from`. Use a re-encoding format for exact cuts.

## Progress

`onProgress()` receives a `Foxws\Media\Process\Progress` about twice a second while ffmpeg runs. It's available on the ffmpeg builder and on `thumbnails()`.

<!-- Progress for a queued job -->
```php
use Foxws\Media\Process\Progress;

$media->ffmpeg()
    ->inFormat(Format::h264())
    ->onProgress(function (Progress $progress) use ($video) {
        $progress->percentage();   // 0-100 across all passes, null when the duration is unknown
        $progress->remaining();    // estimated seconds left, from ffmpeg's speed
        $progress->speed;          // e.g. 2.5 (times real time)

        $video->update(['progress' => $progress->percentage()]);   // or broadcast an event
    })
    ->save('encoded.mp4');
```

- The duration comes from the probe, a clip's length, the sum of the clips for `clips()`, or all files for `concat()`. A single `frame()` reports no percentage.
- Two-pass encodes report one percentage: the first pass is 0-50% and the second 50-100%.
- Callbacks run inside the job, so keep them cheap (throttle database writes or broadcasts yourself if needed).
- **Cancelling:** return `false` from a progress callback, e.g. when the user cancelled. ffmpeg is stopped, nothing is saved, and a `ProcessFailedException` with reason `Cancelled` (not retryable) is thrown.

## Events

Exports dispatch events. Add `->withContext([...])` on the builder or `thumbnails()` so listeners know what an export is about.

| Event | When | Properties |
| --- | --- | --- |
| `Foxws\Media\Events\ProgressReported` | every progress update (about twice a second) | `progress`, `context` |
| `Foxws\Media\Events\ExportCompleted` | after the outputs are on the target disk and `afterSaving` ran | `result` (`ExportResult`), `context`, `duration` |
| `Foxws\Media\Events\ExportFailed` | when an export throws | `exception`, `context` |
| `Foxws\Media\Process\Events\ProcessStarted`/`ProcessCompleted`/`ProcessFailed` | every ffmpeg/ffprobe run | redacted command, `result`, `reason` |

<!-- Broadcasting progress -->
```php
$media->ffmpeg()->withContext(['video_id' => $video->id])->inFormat(Format::h264())->save('encoded.mp4');

// A listener, e.g. in AppServiceProvider::boot()
Event::listen(function (ProgressReported $event) {
    if (isset($event->context['video_id'])) {
        broadcast(new VideoEncoding($event->context['video_id'], $event->progress->percentage()));
    }
});
```

- Progress is requested from ffmpeg as soon as there's a progress callback or a `ProgressReported` listener, so listening alone is enough.
- `thumbnails()` dispatches `ExportCompleted` for its sprite sheets, then writes the VTT file.

## Validating uploads

`Foxws\Media\Rules\MediaFile` probes the uploaded file with ffprobe, so validation relies on what the file really contains rather than its extension or MIME type.

<!-- Upload validation -->
```php
use Foxws\Media\Rules\MediaFile;

$request->validate([
    'video' => ['required', 'file', 'max:2097152', MediaFile::video()
        ->withAudio()
        ->minDuration(1)
        ->maxDuration(3600)
        ->minDimensions(640, 360)
        ->maxDimensions(3840, 2160)
        ->videoCodecs(['h264', 'hevc', 'av1', 'vp9'])
        ->audioCodecs(['aac', 'opus', 'mp3'])],
    'podcast' => ['required', 'file', MediaFile::audio()->maxDuration(7200)],
    'anything' => ['required', 'file', MediaFile::any()],
]);
```

- Files ffprobe can't read, and values that aren't files, fail with "The :attribute must be a readable media file."
- Codec names are ffprobe's (`h264`, `hevc`, `av1`, `vp9`, `aac`, `opus`, ...). Keep the `file`/`max` rules too, so oversized uploads are rejected before probing.

## Packaging into HLS and DASH

Packaging splits already-encoded files into streaming segments with HLS and DASH manifests, written to a disk. It doesn't transcode, so encode renditions first (for example with the ffmpeg builder), then package them. The default `native` driver needs only ffmpeg: it uses the same keyframe-aligned segmenting, playlists and encryption as direct streams. Other drivers can be registered for features it doesn't have.

<!-- Packaging renditions -->
```php
$result = Media::fromDisk('renditions')
    ->open(['1080.mp4', '720.mp4', '480.mp4'])
    ->exportAsStreams()                      // HLS (master.m3u8) and DASH (manifest.mpd) from the same segments
    ->toDisk('streams')
    ->withContext(['video_id' => $video->id])
    ->save("videos/{$video->id}");

$result->path();    // "videos/1/master.m3u8" (manifests come first in paths())
```

- **Shortcuts:** `exportAsHLS()`, `exportAsDASH()` and `exportAsStreams()` probe every opened file, add its video and audio (named `{index}_video.mp4`/`{index}_audio.mp4`), and apply `forVod()`.
- **By hand:** `->package()->addVideoStream($path, 'video/main.mp4')`, `->addAudioStream($path, language: 'eng')`, `->addTextStream('captions/nld.vtt', 'nld.mp4', 'nld')`. Text files can come from anywhere on the source disk.
- **Settings:** `withHlsPlaylist('master.m3u8')`, `withDashManifest('manifest.mpd')`, `segmentDuration(6)`. Driver-specific settings (`fragmentDuration()`, `defaultLanguage()`, `withOption()`/`withOptions()`, ...) are passed on in the `PackagingSpec` for drivers that use them.
- **Shared with the ffmpeg builder:** `toDisk()`, `withVisibility()`, `timeout()`, `withContext()`, save callbacks, `ExportCompleted`/`ExportFailed` events, failure reasons, S3 uploads, rollback and `command()`.
- **Drivers:** `media.packager.default` (`MEDIA_PACKAGER`, default `native`) picks the default; `->using('name')` picks one per export. Register another with `app(PackagerManager::class)->extend('name', fn () => new MyPackager)`, implementing `Foxws\Media\Packaging\Packager`.

### Output of the native driver

- **CMAF only:** fragmented MP4 segments, which HLS and DASH share.
- **Layout:** each stream's output name without its extension is its directory, e.g. `0_video.mp4` gives `0_video/init.mp4`, `0_video/{n}.m4s` and the `0_video.m3u8` media playlist. Playlists and the manifest link everything relatively, also across directories.
- **Subtitles:** DASH gets `{name}.vtt`; HLS gets `{name}.m3u8` with `{name}-hls.vtt`, mapped onto the segment timestamps.
- **Speed:** segments are cut on keyframes with `ffmpeg -c copy`, one run per segment and track, and kept on `media.delivery.cache_disk`. A file that's already been played directly is packaged from the cache without running ffmpeg again. Package long videos in a queued job.
- **Not supported:** driver options, live playlist types, cbcs, key rotation, a clear lead and more than one audio stream throw `InvalidArgumentException`.
- **Under `Media::fake()`**, it writes real playlists and manifests around placeholder segments.

### Encryption

<!-- ClearKey encryption -->
```php
$result = Media::fromDisk('renditions')->open($renditions)
    ->exportAsStreams()
    ->withEncryption()                       // cenc with a new random key unless you pass one
    ->toDisk('streams')
    ->withVisibility('private')
    ->save("videos/{$video->id}");

$video->update([
    'key_id' => $result->encryptionKey()->keyId,       // hex
    'key' => encrypt($result->encryptionKey()->key),   // hex; store it encrypted
]);
```

- **The key file:** the raw 16-byte key is saved next to the segments as `key`. HLS playlists get `#EXT-X-KEY:METHOD=SAMPLE-AES-CTR,KEYFORMAT="identity"` pointing at it, relative to each playlist. Keep the disk private and serve the key only through an authorized route or a short-lived signed URL. `$key->binary()` returns the bytes to respond with.
- **Serving the key yourself:** pass `withEncryption(keyFile: null, keyUri: route('videos.key', $video))` to skip the key file and point playlists at your route.
- **DASH** manifests name the key ID with a ClearKey `ContentProtection` but no license URL, so DASH players need the key themselves, e.g. Shaka Player's `drm.clearKeys`. For a license URL, stream with `Route::mediaStream()` instead.
- **Schemes:** the native driver encrypts with `cenc`; other `ProtectionScheme`s, `withClearLead()` and `withKeyRotation()` need a driver that supports them.
- Keys are redacted from commands, logs and events.

## Serving manifests with signed URLs

Keep packaged segments on a private disk and rewrite the manifests per request, so every URI is a short-lived signed URL. Resolvers receive each file's path on the disk (for example `videos/1/0_video.mp4`), resolved relative to the manifest that references it.

<!-- A manifest controller -->
```php
public function __invoke(Request $request, Video $video, string $path): Response
{
    Gate::authorize('view', $video);

    $media = Media::fromDisk('streams')->open("videos/{$video->id}/{$path}");

    $manifest = str_ends_with($path, '.m3u8')
        ? $media->hlsPlaylist()
            ->resolveKeyUrlsUsing(fn (string $key) => URL::temporarySignedRoute('videos.key', now()->addMinutes(10), [$video]))
            ->resolvePlaylistUrlsUsing(fn (string $playlist) => URL::temporarySignedRoute('videos.manifest', now()->addHours(4), [$video, Str::after($playlist, "videos/{$video->id}/")]))
            ->resolveMediaUrlsUsing(fn (string $file) => Storage::disk('streams')->temporaryUrl($file, now()->addHours(4)))
        : $media->dashManifest()
            ->resolveMediaUrlsUsing(fn (string $file) => Storage::disk('streams')->temporaryUrl($file, now()->addHours(4)));

    return $manifest->toResponse($request);
}
```

- **`hlsPlaylist()`** rewrites:
  - media playlists, including `#EXT-X-MEDIA` and I-frame playlists (`resolvePlaylistUrlsUsing`)
  - segments and `#EXT-X-MAP` init segments (`resolveMediaUrlsUsing`)
  - `#EXT-X-KEY`/`#EXT-X-SESSION-KEY` keys (`resolveKeyUrlsUsing`)

  `all()` returns the master and every media playlist it references, keyed by disk path. `process($path)` rewrites one playlist.
- **`dashManifest()`** rewrites `BaseURL`, `media` and `initialization`/`sourceURL` (`resolveInitUrlsUsing` falls back to the media resolver). Segment templates with `$Number$` are expanded into a segment list, because a template can't produce a different signed URL per segment. Query strings are escaped for XML.
- Without a resolver, URIs stay as they are, and absolute URLs are never changed. Each path is resolved once per request.
- Both are `Responsable` with the right content type, and also usable standalone: `new DynamicHLSPlaylist('streams')->open($path)`. Add cache headers yourself, and keep them shorter than the signed URLs' lifetime.

## Several outputs in one run

`addOutput($path, fn (Output $output) => ...)` writes another file from the same ffmpeg run. Each output has its own `map()`, `inFormat()`, `addFilter()` and `addArgs()`. The inputs are read and decoded once, which is much faster than one run per file.

<!-- Every subtitle track in one run -->
```php
use Foxws\Media\FFMpeg\Output;

$media = Media::fromDisk('s3')->open('videos/movie.mkv');
$builder = $media->ffmpeg()->toDisk('captions');

foreach ($media->probe()->subtitleStreams() as $stream) {
    $builder->addOutput(
        "{$movie->id}/{$stream->index}_{$stream->language}.vtt",
        fn (Output $output) => $output->map("0:{$stream->index}")->inFormat(Format::webVtt()),
    );
}

$result = $builder->save();   // without a path, only the added outputs are written
$result->paths();             // in the order they were declared
```

- `save('main.mp4')` writes the builder's own output first, followed by the added ones. Relative paths, including folders, are kept on the target disk.
- Two-pass encoding and `watermark()` only work with a single output, and throw when combined with `addOutput()`.
- `save()` without a path and without added outputs throws `MediaNotFoundException`.

## Thumbnail sprites and WebVTT

`thumbnails()` samples the first opened video into sprite sheets plus a WebVTT file whose cues point at each tile (`sheet.jpg#xywh=x,y,w,h`). Players use it for seek previews.

<!-- Seek preview thumbnails -->
```php
$result = Media::fromDisk('s3')->open('videos/movie.mp4')->thumbnails()
    ->every(10)                       // or ->count(100, minimumInterval: 5); the default is about 100 thumbnails, at least 1s apart
    ->size(160, 90)                   // letterboxed, keeps the aspect ratio
    ->grid(10, 10)                    // per sheet; more thumbnails continue on the next sheet
    ->format('webp', quality: 75)     // or 'jpg' (the default)
    ->keyframesOnly()                 // optional: decode keyframes only, much faster on long videos
    ->toDisk('storyboards')
    ->withUrl(fn (string $sprite) => Storage::disk('storyboards')->url($sprite))   // optional
    ->save("{$movie->id}/storyboard");

$result->sprites;   // ["1/storyboard_001.webp", ...]
$result->vtt;       // "1/storyboard.vtt"
$result->interval;  // seconds between thumbnails
```

- It's one ffmpeg run with time-based sampling (`fps`), so it doesn't depend on the frame rate.
- When every thumbnail fits on one sheet, the grid shrinks to the thumbnails it holds (2 thumbnails give a 2x1 sheet), so short videos don't get sheets that are mostly black. The result's `columns` and `rows` are the grid that was used.
- `keyframesOnly()` adds `-skip_frame nokey` to the input, so only keyframes are decoded, often 10 to 50 times faster. Each thumbnail shows the last keyframe at or before its time, so with keyframes further apart than the interval, neighbouring thumbnails repeat. It suits seek previews, where speed matters more than the exact frame.
- Without `withUrl()`, cues use the sheet's file name relative to the VTT file, so keep them together.
- Media without a video stream or a known duration throws `InvalidMediaException`. `beforeSaving()`/`afterSaving()` are available, and `afterSaving` receives the `ThumbnailsResult`.

## Filters

Filters are ffmpeg (libavfilter) only. They're plain value objects in `Foxws\Media\Filters` that render filter graph strings, so they don't depend on the builder.

<!-- Filters and watermarks -->
```php
use Foxws\Media\Filters\{Fade, Loudnorm, Position, Scale, Volume};

$media->ffmpeg()
    ->addFilter(Scale::fit(1080, 1920), Fade::in(1), Fade::out(1, start: 29))   // video chain, in order
    ->addFilter(new Loudnorm, Fade::audioIn(0.5))                                // audio chain, in order
    ->watermark('logo.png', disk: 'branding', position: Position::BottomRight, margin: 24, width: 160)
    ->inFormat(Format::h264())
    ->save('reels/1.mp4');
```

- **Video filters:**
  - `Scale::to($width, $height)` (a missing side keeps the aspect ratio)
  - `Scale::fit()` (letterbox to an exact size) and `Scale::fill()` (crop to an exact size)
  - `new Crop(...)`, `new Pad(...)`, `Rotate::clockwise()`/`counterClockwise()`/`upsideDown()`/`flipHorizontally()`/`flipVertically()`
  - `new Fps(30)`, `Fade::in()`/`out()`
- **Audio filters:** `Fade::audioIn()`/`audioOut()`, `Volume::times(0.5)`/`decibels(-6)`, `new Loudnorm(-16, -1.5, 11)`.
- `Custom::video('hqdn3d')` or `Custom::audio('atempo=1.25')` covers any other filter.

### HDR to SDR

HDR video (PQ/HDR10 or HLG) looks washed out when encoded as regular SDR H.264 or turned into images. `$probe->videoStream()->isHdr()` detects it, and `colorTransfer`, `colorPrimaries` and `colorSpace` are available too.

<!-- Tone mapping -->
```php
use Foxws\Media\Filters\{Tonemap, ToneMapAlgorithm};

$media->ffmpeg()
    ->toneMap()                                               // only applied when the source is HDR
    ->addFilter(Scale::to(1280))
    ->inFormat(Format::h264())
    ->save('sdr.mp4');

$media->ffmpeg()->toneMap(new Tonemap(ToneMapAlgorithm::Mobius, desaturation: 0.5));
```

- `toneMap()` is safe to always call for files of unknown origin. It goes first in the video chain, works inside watermark graphs, and in `clips()` only maps the clips whose file is HDR.
- `thumbnails()` tone maps HDR by default, and only the sampled frames, so it stays cheap. Call `->toneMap(null)` to keep the source colours.
- It uses zscale, so ffmpeg needs libzimg. Most static and distribution builds have it, and `ffmpeg -filters | grep zscale` checks.
- Without a watermark, filters become `-vf`/`-af`. A watermark switches to `-filter_complex` with mapped outputs, so it can't be combined with `map()` (that throws `InvalidFilterException`). The watermark is read from the source disk unless another disk is given.

## Formats

Presets: `Format::copy()`, `h264()`, `hevc()`, `av1()`, `vp9()`, audio-only `aac()`, `mp3()`, `opus()`, `flac()`, plus `webVtt()` and `jpeg()`. Formats are immutable, so every method returns a changed copy:

<!-- Rate control and streams -->
```php
use Foxws\Media\Encoding\Format;

Format::h264()->crf(20)->preset('slow');                    // constant quality
Format::h264()->bitrate(2500, max: 3000, buffer: 6000);       // kbit/s; -b:v, -maxrate, -bufsize
Format::h264()->bitrate(2500)->twoPass();                     // two passes, libx264 or libvpx-vp9 only
Format::vp9(crf: 31)->bitrate(1800);                           // VP9 constrained quality
Format::h264()->audioBitrate(128)->audioChannels(2)->sampleRate(48000);
Format::h264()->withoutAudio();                                // also withoutVideo(), withoutSubtitles()
Format::mp3(256);                                              // audio only
```

- Two-pass needs `bitrate()`. Unsupported codecs or a missing bitrate throw `InvalidFormatException` before ffmpeg runs. The first pass's log file never ends up on the target disk.
- `withArguments([...])` appends raw output options. For anything else, use `new Format(container: ..., videoCodec: VideoCodec::..., audioCodec: AudioCodec::...)` with named arguments.

<!-- Encoding on the GPU -->
```php
use Foxws\Media\Encoding\HardwareAcceleration;

$media->ffmpeg()
    ->hardware(HardwareAcceleration::Vaapi)       // or ->hardware() for media.ladder.hardware
    ->addFilter(Scale::to(1280))                  // filters run on the CPU
    ->inFormat(Format::h264(crf: 22))             // becomes h264_vaapi -rc_mode CQP -qp 22
    ->save('encoded.mp4');
```

- **`hardware(?HardwareAcceleration)` on the ffmpeg builder** decodes and filters on the CPU, then uploads the frames at the end of the video chain (`uploadArguments()` before the inputs, `upload()` last in the `-vf` chain, after a watermark's overlay or a reel's joined filters) and encodes with `HardwareAcceleration::encoder()` and `quality()`. `Format::forHardware($hardware)` does the format part: the CRF becomes `quality()` unless a bitrate is set, the preset is dropped, and `-pix_fmt` is replaced (`yuv420p` for NVENC). Only H.264, HEVC and AV1 have hardware encoders.
- **Fallback:** like `makePlayable()`, the builder calls `orCpu()` and encodes with the format as it is when the GPU can't be opened. `acceleration()` tells which one is used. Formats that don't encode video (`Format::copy()`, audio only, `jpeg()`; see `Format::encodesVideo()`) never check the GPU. Two passes with `hardware()` throw `InvalidFormatException`.

## Rendition ladders

`$opener->ladder()` encodes a video into several renditions for adaptive streaming, in one ffmpeg run with an output per rendition:

<!-- A rendition ladder -->
```php
use Foxws\Media\Encoding\{HardwareAcceleration, Ladder, Rendition, VideoCodec};

$result = Media::fromDisk('uploads')->open($upload)
    ->ladder(Ladder::standard(), "videos/{$video->id}/{height}p.mp4")   // 1080p, 720p, 480p, 360p
    ->toDisk('renditions')
    ->onProgress(fn (Progress $progress) => ...)
    ->save();

$result->paths();   // ["videos/1/1080p.mp4", "videos/1/720p.mp4", ...]

// stream them straight away, or package them
Media::fromDisk('renditions')->open($result->paths())->stream();

new Ladder([new Rendition(1440, 9000), new Rendition(720, 3000)], VideoCodec::Hevc, preset: 'slow', audioBitrate: 160);
Ladder::standard()->codec(VideoCodec::Av1)->hardware(HardwareAcceleration::Vaapi)->keyframeInterval(4);

// smaller sizes only, streamed under the untouched source as the top variant
$media = Media::fromDisk('videos')->open('movie.mp4');
$media->ladder(new Ladder([new Rendition(720, 2800), new Rendition(480, 1400)])->alignToSource(), 'renditions/{height}p.mp4')->save();
Media::fromDisk('videos')->open(['movie.mp4', 'renditions/720p.mp4', 'renditions/480p.mp4'])->stream();
```

- **Sizes:** a `Rendition` is the short side and the bitrates (`new Rendition(720, 2800)`; the peak defaults to 7% above, the buffer to twice the target). Portrait video is scaled on its width, so 720p means 720 pixels wide. Renditions larger than the source are skipped, and a source smaller than every rendition gets the smallest one at its own size.
- **Switching:** keyframes are forced every `keyframeInterval` seconds (`media.delivery.segment_duration` by default) with scene-cut keyframes off, so every rendition has keyframes at the same times and direct streams cut the same segments from each. `->alignToSource()` instead forces keyframes where the source's direct stream segments start (from its keyframe index, each segment `keyframeInterval` or longer) and caps other keyframes with `-g 65535`, so the source can be streamed unchanged next to its renditions; stream them with the same segment duration. `->keyframesAt([...seconds])` sets the times by hand.
- **Codecs:** H.264 (default, `medium`), HEVC (tagged `hvc1`) or AV1 (SVT-AV1, preset 8, without a peak bitrate), always with AAC audio in MP4 with `+faststart`.
- **Hardware:** `media.ladder.hardware` (`MEDIA_LADDER_HARDWARE`: `none`, `vaapi`, `nvenc` or `qsv`), or `->hardware()` per ladder, decodes, scales (`scale_vaapi`, `scale_cuda`, `scale_qsv`) and encodes (`h264_vaapi`, `hevc_nvenc`, ...) on the GPU. VAAPI uses `media.ladder.vaapi_device` (`/dev/dri/renderD128`); the container needs access to it.
- **GPU checks and fallback:** before a GPU is used (ladders, renditions on request, `makePlayable()`), `HardwareAcceleration::orCpu()` opens its device once (`-init_hw_device`, cached five minutes in `media.delivery.cache_store`) and falls back to `HardwareAcceleration::None` when it can't, logging ffmpeg's error. Ladders and renditions on request then call `canDecode($media, $videoStream)`: it decodes and scales the source's first frame on the GPU with `-xerror` (cached an hour per codec, profile and pixel format); when that fails (AV1 or 10-bit sources on many GPUs), `Ladder::hardwareDecoding(false)` decodes on the CPU, opens the GPU with `uploadArguments()` and scales `format=nv12,hwupload,scale_vaapi=...` (`hwupload_cuda` for NVENC). `Ladder::inputArguments()` gives the input arguments for either case. `media.ladder.vaapi_device` is used by VAAPI and Quick Sync (`qsv=hw,child_device=...`); `renderD129` is a second GPU. GPU scaling outputs 8-bit 4:2:0 (`format=nv12`, `format=yuv420p` for CUDA). Renditions on request use `media.delivery.hardware` (`MEDIA_DELIVERY_HARDWARE`), defaulting to `media.ladder.hardware`; `HardwareAcceleration::forDelivery()` reads it.
- `ladder()` returns the ffmpeg builder, so `toDisk()`, `onProgress()`, `withContext()`, `timeout()` and save callbacks work as usual. The output pattern takes `{height}` and `{bitrate}`. HDR sources aren't tone mapped.

## Exporting

`save()` writes to a temporary directory, then copies the result to the target disk:
- **S3 disks** (needs `league/flysystem-aws-s3-v3`): files upload concurrently, and large files use multipart uploads that are aborted on failure.
- **Local disks:** files are moved with `rename()`.

Failed copies throw `ExportFailedException`, whose `failures` property lists each file and its error.

## Executables

Each executable resolves lazily: an absolute path from config, or a command name found in the `PATH` or the project root. Only the tools you call need to be installed. Run `php artisan media:info` to see which are found, with their paths and versions. `php artisan about` also has a Media section with the disk, temporary root, timeout and executables. A missing one throws `ExecutableNotFoundException`, which names the env key to set.

### Executables from other packages

Add-on packages bring their own executables by implementing `Foxws\Media\Executables\Binary`, usually on an enum:

<!-- An add-on executable -->
```php
enum EncoderExecutable: string implements Binary
{
    case Encoder = 'encoder';

    public function identifier(): string { return $this->value; }
    public function configuredPath(): string { return Config::string('encoder.path', 'encoder'); }
    public function environmentKey(): string { return 'ENCODER_PATH'; }
    public function versionArguments(): array { return ['--version']; }
}

// in the add-on's service provider
$this->app->make(Executables::class)->register(EncoderExecutable::Encoder);   // lists it in media:info and about
Opener::macro('encoder', fn () => new EncoderBuilder($this));             // $opener->encoder()
```

- `Runner::run($binary, $arguments, environment: ['SVT_LOG' => '1'])` runs it with progress, cancelling, events, logging and redacted keys, like ffmpeg.
- `onOutput` and `onErrorOutput` receive standard output and error output as they stream in; throw `ProcessCancelledException` from either to stop the run. Pass `logWarnings: false` when the program reports progress or results on the error output, so successful runs aren't logged as warnings.
- `Filters\Number::format($number, decimals: 4)` formats numbers for options and filter graphs without trailing zeros or locale separators.
- `Opener` and `MediaFactory` take macros, and the `Media` facade forwards `MediaFactory` macros.
- In tests, `$fake->respondUsing(EncoderExecutable::Encoder, fn (array $arguments) => '...')` fakes its output, and the usual assertions accept any `Binary`. Return `Process::result(output: ..., errorOutput: ..., exitCode: ...)` to stream error output to `onErrorOutput` or fail the run.

## Configuration

Publish with `php artisan vendor:publish --tag=media-config`.

| Key | Purpose |
| --- | --- |
| `disk` | Default disk for `Media::open()` (`MEDIA_DISK`) |
| `packager.default` | Packager driver (`MEDIA_PACKAGER`, `native`) |
| `executables.ffmpeg`, `.ffprobe` | Path or command name (`MEDIA_FFMPEG_PATH`, …) |
| `delivery.segment_duration`, `.cache_store`, `.index_lifetime` | Segment length and keyframe index caching for streaming from stored files |
| `delivery.cache_disk`, `.cache_path`, `.url_lifetime`, `.lock_timeout` | Where packaged segments are cached and how they're served (`media:prune` trims the cache) |
| `delivery.look_ahead`, `.look_ahead_via`, `.look_ahead_connection`, `.look_ahead_queue` | Segments packaged ahead of the player, and where (`queue`, `defer` or `null`) |
| `ladder.hardware`, `.vaapi_device` | GPU encoding for `ladder()` (`MEDIA_LADDER_HARDWARE`: `none`, `vaapi`, `nvenc`, `qsv`), falling back to the CPU when the device can't be opened |
| `delivery.hardware` | GPU for renditions encoded on request (`MEDIA_DELIVERY_HARDWARE`), defaulting to `ladder.hardware` |
| `playback.video_codecs`, `.audio_codecs`, `.video_codec`, `.crf`, `.preset`, `.audio_bitrate`, `.hardware` | What browsers play through a direct stream, and how `makePlayable()` re-encodes the rest |
| `timeout` | Process timeout in seconds; keep it at or below the queue job's `$timeout` |
| `log_channel` | Log channel, `false` to disable |
| `ffmpeg_log_level` | ffmpeg's `-loglevel` (`error`); `warning` logs warnings of successful runs (`MEDIA_FFMPEG_LOG_LEVEL`) |
| `remote_inputs.enabled`, `.url_lifetime` | Read remote disks through signed URLs |
| `temporary_files.root`, `.cache_root` | Where downloads and outputs are written; the cache root is for small files (e.g. `/dev/shm`) |
| `temporary_files.min_free`, `.size_multiplier`, `.cache_min_free` | Fail fast with `InsufficientStorageException` when a root is too full |
| `temporary_files.cleanup_after_jobs` | Delete temporary directories after every queue job (`MEDIA_CLEANUP_AFTER_JOBS`, on) |
| `uploads.concurrency`, `.multipart_threshold`, `.multipart_part_size`, `.multipart_concurrency` | S3 upload tuning |
| `uploads.rollback_on_failure` | Delete what an export already uploaded when another file fails (`MEDIA_UPLOADS_ROLLBACK_ON_FAILURE`, on) |

## Errors, retries and logging

Failed runs throw `Foxws\Media\Exceptions\ProcessFailedException`:
- `$exception->reason` is a `FailureReason` recognised from ffmpeg's error output: `InvalidInput`, `MissingInput`, `UnsupportedCodec`, `InvalidOptions`, `PermissionDenied`, `NoSpace`, `Network`, `Timeout` or `Unknown`.
- `$exception->isRetryable()` is true for `Network`, `Timeout`, `NoSpace` and `Unknown`. Broken uploads, missing codecs or wrong options fail the same way every time.
- When the exception is reported, its `context()` (executable, exit code, reason, redacted command, last 20 lines of error output) appears in the logs and in error trackers such as Sentry, Flare or Nightwatch.

<!-- Retrying only what can recover -->
```php
use Foxws\Media\Exceptions\ProcessFailedException;

public function handle(): void
{
    try {
        Media::fromDisk('s3')->open($this->path)->ffmpeg()->timeout(1800)->inFormat(Format::h264())->save($this->output);
    } catch (ProcessFailedException $exception) {
        $exception->isRetryable() ? $this->release(60) : $this->fail($exception);
    }
}
```

- `->timeout($seconds)` on the builder or `thumbnails()` overrides `media.timeout`. Keep it below the job's `$timeout`, so the package stops ffmpeg and throws a `Timeout` failure instead of the worker being killed.
- Failures are logged as errors with the same context. Successful runs that still wrote to the error output are logged as warnings (unless `Runner::run()` gets `logWarnings: false`). Set `MEDIA_FFMPEG_LOG_LEVEL=warning` to see why output from damaged files looks wrong.
- **Events:** `Process\Events\ProcessStarted`, `ProcessCompleted` and `ProcessFailed` (with `$result` and `$reason`) are dispatched for every ffmpeg/ffprobe run, with the command redacted.
- **Other exceptions** live in `Foxws\Media\Exceptions` too: `ExecutableNotFoundException`, `InvalidMediaException`, `InvalidFormatException`, `InvalidFilterException`, `ExportFailedException`, `InsufficientStorageException` and `TemporaryFileException`.

## Queue jobs, crashes and cleanup

- **After every job:** temporary directories are deleted when a queue job finishes or throws (`media.temporary_files.cleanup_after_jobs`, on by default), and at the end of each request.
- **Timeouts:** when a job times out, Laravel's worker kills itself with SIGKILL. The package listens for `JobTimedOut` and `WorkerStopping` first, stops the running ffmpeg (SIGTERM, then SIGKILL) so it doesn't keep running as an orphan, and deletes the temporary directories. Still give long encodes a `->timeout()` below the job's `$timeout`, so they fail cleanly with a retryable `Timeout` instead.
- **Crashes:** schedule `media:clean` to remove directories left behind by OOM kills, crashes or power loss. It only deletes the package's own directories (16 hex characters) that haven't been written to for longer than `--older-than` minutes (default: `media.timeout` plus an hour), so it's safe on shared mounts such as `/dev/shm`. `--dry-run` lists them.
- **Partial exports:** when one file of an export fails to upload, the files of that export that did reach the disk are deleted (`media.uploads.rollback_on_failure`), so retries start clean.
- **Overlapping jobs:** to stop two jobs from processing the same media at once, use Laravel's `WithoutOverlapping` job middleware, keyed by the model.

<!-- Scheduling media:clean -->
```php
// routes/console.php
Schedule::command('media:clean')->hourly();
Schedule::command('media:prune')->daily();

// a job that shouldn't overlap for the same video
public function middleware(): array
{
    return [(new WithoutOverlapping($this->video->id))->expireAfter(7200)];
}
```

## Testing

Use `Media::fake()`. Nothing is executed and no ffmpeg is needed: probes return fake data, ffmpeg writes placeholder files to the (faked) target disk, and every command is recorded. Events, failures and progress behave as in production, and Laravel's `Process` facade is left alone.

<!-- Testing with Media::fake() -->
```php
use Foxws\Media\Executables\Executable;
use Foxws\Media\Facades\Media;
use Foxws\Media\Testing\FakeProbe;

Storage::fake('videos');
Storage::fake('clips');

Media::fake([
    'uploads/movie.mkv' => FakeProbe::video(duration: 120, subtitles: ['eng', 'nld']),   // keyed by the end of the path
    'uploads/song.mp3' => FakeProbe::audio(duration: 200),
])->scenes('uploads/movie.mkv', [12.5, 40.0]);                                         // fake scene changes

CreateClip::run($video);   // the code under test

Media::assertProbed('uploads/movie.mkv');
Media::assertSaved('clips/1.mp4', 'clips');
Media::assertRan(Executable::FFMpeg, fn (array $arguments) => in_array('libx264', $arguments, true));
Media::assertNotRan(Executable::FFMpeg, fn (array $arguments) => in_array('-pass', $arguments, true));
```

- Unknown paths probe as a one-minute 1080p H.264 video with AAC audio. Use `'*'` as the key to fake every probe, e.g. for uploads, which have random temporary names: `Media::fake(['*' => FakeProbe::video(duration: 5)])` makes `MediaFile::video()->minDuration(10)` fail. `FakeProbe::video()` also takes `width`, `height`, `codec`, `audio: false`, `subtitles`, `audioLanguages`, `transfer: 'smpte2084'` (HDR) and `frameRate`.
- `$fake->failNext(Executable::FFMpeg, 'Invalid data found')` (on the fake `Media::fake()` returned; calling it again starts a new fake) makes the next run throw `ProcessFailedException`. Use it to test failure handling and retries.
- `$fake->respondUsing($binary, fn (array $arguments) => $output)` fakes the output of an add-on's executable, or its error output and exit code with `Process::result()`; without it, add-on executables run as successful with no output.
- `onProgress()` callbacks receive 50% and 100%. Probes and scenes are matched against the end of the opened path.
- Other assertions: `assertRanTimes()`, `assertNothingRan()`, `assertNotSaved()`. `Media::fake()` returns the fake, whose `commands(Executable::FFMpeg)` lists the recorded arguments.
