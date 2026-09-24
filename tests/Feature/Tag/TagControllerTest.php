<?php

declare(strict_types=1);

use Domain\Tags\Enums\TagType;
use Domain\Tags\Models\Tag;
use Domain\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Web\Tags\Controllers\TagController;

uses(RefreshDatabase::class);

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
