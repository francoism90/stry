<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Broadcast;
use Modules\Api\Groups\Broadcasting\GroupChannel;
use Modules\Api\Media\Broadcasting\MediaChannel;
use Modules\Api\Playlists\Broadcasting\PlaylistChannel;
use Modules\Api\Profiles\Broadcasting\ProfileChannel;
use Modules\Api\Tags\Broadcasting\TagChannel;
use Modules\Api\Transcodes\Broadcasting\TranscodeChannel;
use Modules\Api\Users\Broadcasting\UserChannel;
use Modules\Api\Videos\Broadcasting\VideoChannel;
use Modules\Api\Videos\Broadcasting\VideoLibraryChannel;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('users.{user}', UserChannel::class);
Broadcast::channel('profiles.{profile}', ProfileChannel::class);
Broadcast::channel('tags.{tag}', TagChannel::class);
Broadcast::channel('videos.{video}', VideoChannel::class);
Broadcast::channel('library', VideoLibraryChannel::class);
Broadcast::channel('groups.{group}', GroupChannel::class);
Broadcast::channel('media.{media}', MediaChannel::class);
Broadcast::channel('playlists.{playlist}', PlaylistChannel::class);
Broadcast::channel('transcodes.{transcode}', TranscodeChannel::class);
