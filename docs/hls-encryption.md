# HLS Encryption Implementation

## Overview

Simple key encryption (AES-128) has been implemented for HLS video streaming using Shaka Packager. This provides content protection for video playlists.

## Architecture

### 1. Database Schema

Added encryption fields to the `playlists` table:
- `encryption_key_id` - 128-bit key ID (hex)
- `encryption_key` - 128-bit encryption key (hex)

**Migration:** `database/migrations/2025_12_19_202817_add_encryption_to_playlists_table.php`

### 2. Encryption Key Generator

**Location:** `vendor/foxws/laravel-shaka/src/Support/EncryptionKeyGenerator.php` (part of laravel-shaka package)

Provides static methods for:
- `generate()` - Generate both key ID and key
- `generateKey()` - Generate 128-bit encryption key
- `generateKeyId()` - Generate 128-bit key ID
- `formatForShaka()` - Format keys for Shaka Packager
- `writeKeyFile()` - Write key to disk storage

### 3. Video Playlist Generation

**Location:** `src/Domain/Videos/Actions/CreateNewVideoPlaylist.php`

**Process:**
1. Generate encryption keys using `EncryptionKeyGenerator`
2. Store key ID and key in database
3. Write binary key file to storage
4. Configure Shaka Packager with encryption:
   - `keys` parameter with formatted key/key_id
   - `hls_key_uri` pointing to key file path

### 4. Encryption Key Delivery

**Controller:** `src/App/Api/Playlists/Controllers/PlaylistEncryptionKeyController.php`

**Route:** `GET /api/v1/play/{playlist}/key/{path}`

**Process:**
1. Verify playlist has encryption enabled
2. Retrieve key file from storage
3. Return binary key with proper headers
4. Apply signed URL protection

### 5. HLS Playlist Resolution

**Controller:** `src/App/Api/Playlists/Controllers/PlaylistManifestController.php`

The `getKeyUrlResolver()` method in the Playlist model generates signed URLs for encryption keys that are embedded in HLS playlists via the `#EXT-X-KEY` tag.

## Example Usage

### Creating Encrypted Playlist

```php
use Foxws\Shaka\Support\EncryptionKeyGenerator;
use Foxws\Shaka\Facades\Shaka;

// Generate encryption keys
$encryption = EncryptionKeyGenerator::generate();

// Store in database
$playlist = $video->playlists()->create([
    'encryption_key_id' => $encryption['key_id'],
    'encryption_key' => $encryption['key'],
    // ...
]);

// Write key file
EncryptionKeyGenerator::writeKeyFile(
    $playlist->getDisk(),
    $playlist->getPath('encryption.key'),
    $encryption['key']
);

// Package with encryption
Shaka::fromDisk('videos')
    ->open('input.mp4')
    ->export()
    ->toDisk('export')
    ->outputPath($playlist->getPath())
    ->addVideoStream('input.mp4', 'video.mp4')
    ->addAudioStream('input.mp4', 'audio.mp4')
    ->withHlsMasterPlaylist('master.m3u8')
    ->withEncryption([
        'keys' => EncryptionKeyGenerator::formatForShaka(
            $encryption['key_id'],
            $encryption['key']
        ),
        'hls_key_uri' => 'encryption.key',
    ])
    ->save();
```

### HLS Playlist Output

The generated HLS playlist will contain:

```m3u8
#EXTM3U
#EXT-X-VERSION:6
#EXT-X-KEY:METHOD=AES-128,URI="https://app.test/api/v1/play/{playlist}/key/encryption.key?signature=..."
#EXTINF:6.000000,
segment_00001.m4s
#EXTINF:6.000000,
segment_00002.m4s
```

## Security Features

1. **Unique Keys Per Playlist** - Each playlist gets its own encryption key
2. **Signed URLs** - Key delivery URLs are time-limited and signed
3. **Secure Storage** - Keys stored in database and binary files on disk
4. **Middleware Protection** - Configurable middleware on key delivery endpoint
5. **Authorization** - Can add Gate checks for access control

## To Apply Changes

Run the migration to add encryption fields:

```bash
php artisan migrate
```

## Notes

- Keys are 128-bit (16 bytes) for AES-128 encryption
- Key files are binary (not hex) for HLS player compatibility
- Shaka Packager requires hex format for command-line arguments
- Dynamic URL resolution handles key URI in playlists automatically
