<?php

declare(strict_types=1);

use Domain\Transcodes\Models\Transcode;
use Domain\Videos\Jobs\ImportVideo;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;

beforeEach(fn () => Storage::fake('transcodes'));

it('does nothing when no completed transcodes exist', function () {
    Transcode::factory()->create(); // pending

    $this->artisan('transcodes:import')
        ->expectsOutputToContain('No completed transcodes found to import.')
        ->assertSuccessful();
});

it('does not import failed transcodes', function () {
    Transcode::factory()->failed()->create();

    $this->artisan('transcodes:import')
        ->expectsOutputToContain('No completed transcodes found to import.')
        ->assertSuccessful();
});

it('does not import already imported transcodes', function () {
    Transcode::factory()->imported()->create();

    $this->artisan('transcodes:import')
        ->expectsOutputToContain('No completed transcodes found to import.')
        ->assertSuccessful();
});

it('imports completed transcodes', function () {
    $transcode = Transcode::factory()->completed()->create();

    $this->artisan('transcodes:import')
        ->expectsOutputToContain('Transcodes imported successfully.')
        ->assertSuccessful();

    Bus::assertDispatched(ImportVideo::class);

    expect(Transcode::query()->find($transcode->id)->isImported())->toBeTrue();
});

it('imports multiple completed transcodes', function () {
    Transcode::factory()->completed()->count(3)->create();

    $this->artisan('transcodes:import')
        ->expectsOutputToContain('Transcodes imported successfully.')
        ->assertSuccessful();

    Bus::assertDispatchedTimes(ImportVideo::class, 3);
});
