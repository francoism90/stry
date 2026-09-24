<?php

declare(strict_types=1);

use Domain\Groups\Models\Group;
use Domain\Playlists\Models\Playlist;
use Domain\Profiles\Models\Profile;
use Domain\Tags\Models\Tag;
use Domain\Transcodes\Models\Transcode;
use Domain\Users\Models\User;
use Domain\Videos\Models\Video;

it('broadcasts model events on a pluralised channel with a singular event name', function (string $model, string $channel, string $event) {
    $instance = $model::factory()->create();

    expect($instance->broadcastChannel())->toBe("{$channel}.{$instance->ulid}")
        ->and($instance->broadcastAs('updated'))->toBe("{$event}.updated")
        ->and($instance->broadcastWith('updated'))->toBe(['id' => $instance->ulid])
        ->and($instance->broadcastQueue())->toBe('broadcasts')
        ->and($instance->broadcastAfterCommit())->toBeTrue();
})->with([
    [Group::class, 'groups', 'group'],
    [Playlist::class, 'playlists', 'playlist'],
    [Profile::class, 'profiles', 'profile'],
    [Tag::class, 'tags', 'tag'],
    [Transcode::class, 'transcodes', 'transcode'],
    [User::class, 'users', 'user'],
    [Video::class, 'videos', 'video'],
]);

it('finds models from their ulid and uses it as route key', function () {
    $video = Video::factory()->create();

    expect($video->getRouteKey())->toBe($video->ulid)
        ->and(Video::findFromUlid($video->ulid))->toBeSameModel($video)
        ->and(Video::findFromUlid($video))->toBe($video)
        ->and(Video::findFromUlid('missing'))->toBeNull();
});
