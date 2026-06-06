<?php

declare(strict_types=1);

use Domain\Transcodes\Models\Transcode;
use Illuminate\Support\Facades\Storage;

beforeEach(fn () => Storage::fake('transcodes'));

it('does nothing when no failed transcodes exist', function () {
    Transcode::factory()->create(); // pending

    $this->artisan('transcodes:clear')
        ->expectsOutputToContain('No transcodes found to delete.')
        ->assertSuccessful();
});

it('shows failed transcodes and deletes on confirmation', function () {
    $transcode = Transcode::factory()->failed()->create();

    $this->artisan('transcodes:clear')
        ->expectsConfirmation('Are you sure you want to delete these transcodes?', 'yes')
        ->expectsOutputToContain("deleting transcode ({$transcode->getKey()})")
        ->assertSuccessful();

    expect(Transcode::query()->count())->toBe(0);
});

it('does not delete when confirmation is declined', function () {
    Transcode::factory()->failed()->create();

    $this->artisan('transcodes:clear')
        ->expectsConfirmation('Are you sure you want to delete these transcodes?', 'no')
        ->assertSuccessful();

    expect(Transcode::query()->count())->toBe(1);
});

it('does not show pending transcodes by default', function () {
    Transcode::factory()->create(); // pending state
    Transcode::factory()->failed()->create();

    $this->artisan('transcodes:clear')
        ->expectsConfirmation('Are you sure you want to delete these transcodes?', 'no')
        ->assertSuccessful();

    expect(Transcode::query()->count())->toBe(2);
});

it('clears expired transcodes with --all option', function () {
    $expired = Transcode::factory()->expired()->create();
    $pending = Transcode::factory()->create();

    $this->artisan('transcodes:clear --all')
        ->expectsConfirmation('Are you sure you want to delete these transcodes?', 'yes')
        ->assertSuccessful();

    expect(Transcode::query()->count())->toBe(1)
        ->and(Transcode::query()->first()->getKey())->toBe($pending->getKey());
});

it('does not include recent failed transcodes in --all option', function () {
    // Recent failed (not expired - less than 7 days old)
    Transcode::factory()->failed()->create();

    $this->artisan('transcodes:clear --all')
        ->expectsOutputToContain('No transcodes found to delete.')
        ->assertSuccessful();
});
