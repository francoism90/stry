<?php

declare(strict_types=1);

use App\Api\Tags\Broadcasting\TagChannel;
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
