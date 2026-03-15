<?php

declare(strict_types=1);

use App\Api\Groups\Broadcasting\GroupChannel;
use App\Api\Media\Broadcasting\MediaChannel;
use App\Api\Playlists\Broadcasting\PlaylistChannel;
use App\Api\Tags\Broadcasting\TagChannel;
use App\Api\Transcodes\Broadcasting\TranscodeChannel;
use App\Api\Users\Broadcasting\UserChannel;
use App\Api\Videos\Broadcasting\VideoChannel;
use Illuminate\Support\Facades\Broadcast;

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
Broadcast::channel('tags.{tag}', TagChannel::class);
Broadcast::channel('videos.{video}', VideoChannel::class);
Broadcast::channel('groups.{group}', GroupChannel::class);
Broadcast::channel('media.{media}', MediaChannel::class);
Broadcast::channel('playlists.{playlist}', PlaylistChannel::class);
Broadcast::channel('transcodes.{transcode}', TranscodeChannel::class);
