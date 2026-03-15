<?php

declare(strict_types=1);

use Domain\Groups\Models\Group;
use Domain\Users\Models\User;
use Domain\Videos\Models\Video;

it('detaches all videos from the viewed group by default', function () {
    $user = User::factory()->create();
    $group = Group::factory()->for($user)->viewed()->create();
    $video = Video::factory()->create();
    $group->videos()->attach($video);

    $options = User::query()->limit(10)->pluck('email', 'id')->all();

    $this->artisan('groups:clear')
        ->expectsSearch('Select user to clear group for', $user->id, '', $options)
        ->expectsConfirmation('Are you sure you want to detach all 1 video(s) from the Viewed group?', 'yes')
        ->assertSuccessful();

    expect($group->fresh()->videos()->count())->toBe(0);
});

it('detaches all videos from the specified group type', function () {
    $user = User::factory()->create();
    $group = Group::factory()->for($user)->saved()->create();
    $video = Video::factory()->create();
    $group->videos()->attach($video);

    $options = User::query()->limit(10)->pluck('email', 'id')->all();

    $this->artisan('groups:clear', ['--group' => 'saved'])
        ->expectsSearch('Select user to clear group for', $user->id, '', $options)
        ->expectsConfirmation('Are you sure you want to detach all 1 video(s) from the Saved group?', 'yes')
        ->assertSuccessful();

    expect($group->fresh()->videos()->count())->toBe(0);
});

it('rejects unsupported group types', function (string $type) {
    $this->artisan('groups:clear', ['--group' => $type])
        ->assertSuccessful();
})->with(['custom', 'mixer', 'invalid']);

it('reports when the group is already empty', function () {
    $user = User::factory()->create();
    Group::factory()->for($user)->viewed()->create();

    $options = User::query()->limit(10)->pluck('email', 'id')->all();

    $this->artisan('groups:clear')
        ->expectsSearch('Select user to clear group for', $user->id, '', $options)
        ->expectsOutputToContain('already empty')
        ->assertSuccessful();
});

it('does nothing when confirmation is declined', function () {
    $user = User::factory()->create();
    $group = Group::factory()->for($user)->viewed()->create();
    $video = Video::factory()->create();
    $group->videos()->attach($video);

    $options = User::query()->limit(10)->pluck('email', 'id')->all();

    $this->artisan('groups:clear')
        ->expectsSearch('Select user to clear group for', $user->id, '', $options)
        ->expectsConfirmation('Are you sure you want to detach all 1 video(s) from the Viewed group?', 'no')
        ->assertSuccessful();

    expect($group->fresh()->videos()->count())->toBe(1);
});
