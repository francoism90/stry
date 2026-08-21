<?php

declare(strict_types=1);

use App\Web\Videos\Controllers\VideoController;
use Domain\Transcodes\Models\Transcode;
use Domain\Users\Models\User;
use Domain\Videos\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(fn () => Storage::fake('transcodes'));

it('defers the transcodes prop with the video transcodes', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');
    $video = Video::factory()->create();
    $transcode = Transcode::factory()->for($video, 'transcodable')->create();

    $response = $this->actingAs($user)->get(action([VideoController::class, 'show'], $video));

    $response->assertInertia(fn (Assert $page) => $page
        ->missing('transcodes')
        ->loadDeferredProps(fn (Assert $reload) => $reload
            ->has('transcodes', 1)
            ->where('transcodes.0.id', $transcode->getRouteKey())
        ));
});

it('limits the transcodes prop to the ten most recent transcodes', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');
    $video = Video::factory()->create();
    Transcode::factory()->count(12)->for($video, 'transcodable')->create();

    $response = $this->actingAs($user)->get(action([VideoController::class, 'show'], $video));

    $response->assertInertia(fn (Assert $page) => $page
        ->loadDeferredProps(fn (Assert $reload) => $reload
            ->has('transcodes', 10)
        ));
});

it('does not include transcodes belonging to other videos', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');
    $video = Video::factory()->create();
    $other = Video::factory()->create();
    Transcode::factory()->for($other, 'transcodable')->create();

    $response = $this->actingAs($user)->get(action([VideoController::class, 'show'], $video));

    $response->assertInertia(fn (Assert $page) => $page
        ->loadDeferredProps(fn (Assert $reload) => $reload
            ->has('transcodes', 0)
        ));
});

it('returns no transcodes for users without permission to view any transcode', function () {
    $user = User::factory()->create();
    $video = Video::factory()->create();
    Transcode::factory()->for($video, 'transcodable')->create();

    $response = $this->actingAs($user)->get(action([VideoController::class, 'show'], $video));

    $response->assertInertia(fn (Assert $page) => $page
        ->loadDeferredProps(fn (Assert $reload) => $reload
            ->has('transcodes', 0)
        ));
});
