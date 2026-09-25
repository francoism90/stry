<?php

declare(strict_types=1);

use Domain\Playlists\Models\Playlist;
use Illuminate\Support\Facades\Storage;

beforeEach(fn () => Storage::fake('segments'));

it('deletes prunable playlists on confirmation', function () {
    Playlist::factory()->failed()->create();

    $this->artisan('playlists:clear')
        ->expectsConfirmation('Are you sure you want to delete these playlists?', 'yes')
        ->assertSuccessful();

    expect(Playlist::query()->count())->toBe(0);
});

it('deletes prunable playlists without confirmation when using --force', function () {
    Playlist::factory()->failed()->create();

    $this->artisan('playlists:clear --force')
        ->assertSuccessful();

    expect(Playlist::query()->count())->toBe(0);
});
