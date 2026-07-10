<?php

declare(strict_types=1);

use Domain\Tags\Enums\TagType;
use Domain\Tags\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\artisan;

uses(RefreshDatabase::class);

it('creates a tag using the app locale by default', function () {
    artisan('tags:create')
        ->expectsQuestion('Name', 'Documentary')
        ->expectsQuestion('Type', TagType::Genre->value)
        ->assertSuccessful();

    $tag = Tag::query()->where('name->'.app()->getLocale(), 'Documentary')->firstOrFail();

    expect($tag->type)->toBe(TagType::Genre);
});

it('creates a tag using the given --locale option', function () {
    artisan('tags:create --locale=nl')
        ->expectsQuestion('Name', 'Documentaire')
        ->expectsQuestion('Type', TagType::Genre->value)
        ->assertSuccessful();

    $tag = Tag::query()->where('name->nl', 'Documentaire')->firstOrFail();

    expect($tag->type)->toBe(TagType::Genre);
});
