<?php

declare(strict_types=1);

use App\Web\Videos\Controllers\VideoController;
use Domain\Users\Models\User;
use Domain\Videos\Models\Video;
use Domain\Videos\States\Failed;
use Domain\Videos\States\Verified;

// index

it('allows super-admins to view the video library', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $response = $this->actingAs($user)->get(action([VideoController::class, 'index']));

    $response->assertSuccessful();
});

it('redirects guests from viewing the video library', function () {
    $response = $this->get(action([VideoController::class, 'index']));

    $response->assertRedirect();
});

it('forbids regular users from viewing the video library', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(action([VideoController::class, 'index']));

    $response->assertForbidden();
});

// update

it('allows super-admins to change a video state', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $video = Video::factory()->create(['name' => ['en' => 'Original'], 'state' => Verified::class]);

    $response = $this->actingAs($user)->put(action([VideoController::class, 'update'], $video), [
        'name' => 'Original',
        'state' => 'failed',
    ]);

    $response->assertRedirect();
    expect($video->fresh()->state->equals(Failed::class))->toBeTrue();
});

it('allows a regular owner to change their own video state', function () {
    $user = User::factory()->create();
    $video = Video::factory()->for($user)->create(['name' => ['en' => 'Original'], 'state' => Verified::class]);

    $response = $this->actingAs($user)->put(action([VideoController::class, 'update'], $video), [
        'name' => 'Original',
        'state' => 'failed',
    ]);

    $response->assertRedirect();
    expect($video->fresh()->state->equals(Failed::class))->toBeTrue();
});

it('forbids a non-owner from updating a video', function () {
    $user = User::factory()->create();
    $video = Video::factory()->create(['name' => ['en' => 'Original'], 'state' => Verified::class]);

    $response = $this->actingAs($user)->put(action([VideoController::class, 'update'], $video), [
        'name' => 'Updated',
        'state' => 'failed',
    ]);

    $response->assertForbidden();
    expect($video->fresh())
        ->name->toBe('Original')
        ->state->equals(Verified::class)->toBeTrue();
});
