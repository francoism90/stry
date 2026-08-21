<?php

declare(strict_types=1);

use App\Web\Transcodes\Controllers\TranscodeController;
use Domain\Transcodes\Enums\TranscodeSorter;
use Domain\Transcodes\Models\Transcode;
use Domain\Users\Models\User;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(fn () => Storage::fake('transcodes'));

// index

it('allows admins to view the transcode index', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $response = $this->actingAs($user)->get(action([TranscodeController::class, 'index']));

    $response->assertSuccessful();
});

it('exposes the available sorters on the transcode index', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $response = $this->actingAs($user)->get(action([TranscodeController::class, 'index']));

    $response->assertInertia(fn (Assert $page) => $page
        ->has('sorters', count(TranscodeSorter::cases()))
        ->where('sorters.0.value', TranscodeSorter::Newest->value)
    );
});

it('redirects guests from viewing the transcode index', function () {
    $response = $this->get(action([TranscodeController::class, 'index']));

    $response->assertRedirect();
});

it('forbids regular users from viewing the transcode index', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(action([TranscodeController::class, 'index']));

    $response->assertForbidden();
});

// destroy

it('allows admins to delete any transcode', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $other = User::factory()->create();
    $transcode = Transcode::factory()->create(['user_id' => $other->getKey()]);

    $response = $this->actingAs($user)->delete(action([TranscodeController::class, 'destroy'], $transcode));

    $response->assertRedirect();
    expect(Transcode::query()->find($transcode->getKey()))->toBeNull();
});

it('allows the owner to delete their transcode', function () {
    $user = User::factory()->create();
    $transcode = Transcode::factory()->create(['user_id' => $user->getKey()]);

    $response = $this->actingAs($user)->delete(action([TranscodeController::class, 'destroy'], $transcode));

    $response->assertRedirect();
    expect(Transcode::query()->find($transcode->getKey()))->toBeNull();
});

it('forbids other users from deleting a transcode they do not own', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $transcode = Transcode::factory()->create(['user_id' => $owner->getKey()]);

    $response = $this->actingAs($other)->delete(action([TranscodeController::class, 'destroy'], $transcode));

    $response->assertForbidden();
    expect(Transcode::query()->find($transcode->getKey()))->not->toBeNull();
});
