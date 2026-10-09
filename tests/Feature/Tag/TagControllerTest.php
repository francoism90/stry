<?php

declare(strict_types=1);

use Domain\Tags\Enums\TagType;
use Domain\Tags\Models\Tag;
use Domain\Users\Models\User;
use Domain\Videos\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Web\Tags\Controllers\TagController;

uses(RefreshDatabase::class);

it('looks up the tag thumbnails in the same number of queries however many tags are listed', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $countTagQueries = function () use ($user): int {
        Tag::factory()->create()->videos()->attach(tap(Video::factory()->create(), createClipFor(...)));

        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->actingAs($user)->get(action([TagController::class, 'index']))->assertOk();

        return collect(DB::getQueryLog())->filter(fn (array $query) => str_contains($query['query'], 'taggables'))->count();
    };

    $withOneTag = $countTagQueries();

    $countTagQueries();

    expect($countTagQueries())->toBe($withOneTag);
});

it('allows a super-admin to create a tag', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $response = $this->actingAs($user)->post(action([TagController::class, 'store']), [
        'name' => 'Documentary',
        'type' => TagType::Genre->value,
    ]);

    $response->assertRedirect();
    $response->assertInertiaFlash('type', 'success');

    $tag = Tag::query()->where('name->'.app()->getLocale(), 'Documentary')->firstOrFail();

    expect($tag->type)->toBe(TagType::Genre);
});

it('stores the description when creating a tag', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $this->actingAs($user)->post(action([TagController::class, 'store']), [
        'name' => 'Documentary',
        'type' => TagType::Genre->value,
        'description' => 'Films about real events.',
    ]);

    $tag = Tag::query()->where('name->'.app()->getLocale(), 'Documentary')->firstOrFail();

    expect($tag->description)->toBe('Films about real events.');
});

it('sorts tags after creating one', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $zebra = Tag::factory()->create(['name' => ['en' => 'Zebra'], 'type' => TagType::Genre]);

    $this->actingAs($user)->post(action([TagController::class, 'store']), [
        'name' => 'Aardvark',
        'type' => TagType::Genre->value,
    ]);

    $aardvark = Tag::query()->where('name->en', 'Aardvark')->firstOrFail();

    expect($aardvark->order_column)->toBeLessThan($zebra->refresh()->order_column);
});

it('denies a regular user from creating a tag', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(action([TagController::class, 'store']), [
        'name' => 'Documentary',
        'type' => TagType::Genre->value,
    ]);

    $response->assertForbidden();
});

it('sorts tags after updating one', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $tag = Tag::factory()->create(['name' => ['en' => 'Zebra'], 'type' => TagType::Genre, 'user_id' => $user->getKey()]);
    $other = Tag::factory()->create(['name' => ['en' => 'Aardvark'], 'type' => TagType::Genre]);

    $this->actingAs($user)->patch(action([TagController::class, 'update'], $tag), [
        'name' => 'Aardvark 2',
        'type' => TagType::Genre->value,
    ]);

    expect($tag->refresh()->order_column)->toBeGreaterThan($other->refresh()->order_column);
});

it('redirects to the tag index after deleting a tag', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $tag = Tag::factory()->create();

    $response = $this->actingAs($user)
        ->from(action([TagController::class, 'show'], $tag))
        ->delete(action([TagController::class, 'destroy'], $tag));

    $response->assertRedirectToRoute('tags.index');
    $response->assertInertiaFlash('type', 'warning');
    $this->assertModelMissing($tag);
});

it('denies a regular user from deleting a tag', function () {
    $user = User::factory()->create();

    $tag = Tag::factory()->create();

    $response = $this->actingAs($user)->delete(action([TagController::class, 'destroy'], $tag));

    $response->assertForbidden();
    $this->assertModelExists($tag);
});

it('stores an uploaded picture when updating a tag', function () {
    Storage::fake('conversions');

    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $tag = Tag::factory()->create(['type' => TagType::Genre]);

    $response = $this->actingAs($user)->patch(action([TagController::class, 'update'], $tag), [
        'name' => 'Documentary',
        'type' => TagType::Genre->value,
        'avatar' => UploadedFile::fake()->image('poster.jpg', 640, 360),
    ]);

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();

    expect($tag->refresh()->getFirstMedia('avatar'))->not->toBeNull()
        ->and($tag->getFirstMedia('avatar')->file_name)->toBe('poster.jpg');
});

it('removes the picture when updating a tag', function () {
    Storage::fake('conversions');

    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $tag = Tag::factory()->create(['type' => TagType::Genre]);
    $tag->addMedia(UploadedFile::fake()->image('poster.jpg'))->toMediaCollection('avatar');

    $this->actingAs($user)->patch(action([TagController::class, 'update'], $tag), [
        'name' => 'Documentary',
        'type' => TagType::Genre->value,
        'remove_avatar' => true,
    ]);

    expect($tag->refresh()->getFirstMedia('avatar'))->toBeNull();
});

it('rejects a picture that is not an image', function () {
    Storage::fake('conversions');

    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $tag = Tag::factory()->create(['type' => TagType::Genre]);

    $response = $this->actingAs($user)->patch(action([TagController::class, 'update'], $tag), [
        'name' => 'Documentary',
        'type' => TagType::Genre->value,
        'avatar' => UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf'),
    ]);

    $response->assertSessionHasErrors('avatar');

    expect($tag->refresh()->getFirstMedia('avatar'))->toBeNull();
});
