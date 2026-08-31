<?php

declare(strict_types=1);

use Domain\Users\Models\User;
use Foundation\Settings\GeneralSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

it('shares application settings with a super-admin', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $response = $this->actingAs($user)->get('/');

    $response->assertInertia(fn (Assert $page) => $page
        ->where('settings.site_name', app(GeneralSettings::class)->site_name)
    );
});

it('does not share application settings with a regular user', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/');

    $response->assertInertia(fn (Assert $page) => $page->where('settings', null));
});
