<?php

declare(strict_types=1);

use Domain\Users\Models\User;
use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Support\Facades\DB;
use Modules\Web\Home\Controllers\HomeController;

beforeEach(function () {
    $this->admin = User::factory()->create();
    $this->admin->assignRole('super-admin');
});

function forgetLatestMigration(): void
{
    DB::table('migrations')->where('id', DB::table('migrations')->max('id'))->delete();
}

it('shares no notices with users who cannot manage the application', function () {
    $user = User::factory()->create();

    forgetLatestMigration();
    config(['filesystems.fingerprint' => 'stale']);

    $this->actingAs($user)
        ->get(action(HomeController::class))
        ->assertInertia(fn ($page) => $page->where('notices', null));
});

it('shares no notices with admins when the application is up to date', function () {
    $this->actingAs($this->admin)
        ->get(action(HomeController::class))
        ->assertInertia(fn ($page) => $page
            ->where('notices.restart', false)
            ->where('notices.migrations', false)
        );
});

it('tells admins to restart when the storage disks changed after the workers booted', function () {
    config(['filesystems.fingerprint' => 'stale']);

    $this->actingAs($this->admin)
        ->get(action(HomeController::class))
        ->assertInertia(fn ($page) => $page->where('notices.restart', true));
});

it('tells admins when migrations are pending', function () {
    forgetLatestMigration();

    $this->actingAs($this->admin)
        ->get(action(HomeController::class))
        ->assertInertia(fn ($page) => $page->where('notices.migrations', true));
});

it('checks the pending migrations again after migrations ran', function () {
    $this->actingAs($this->admin)
        ->get(action(HomeController::class))
        ->assertInertia(fn ($page) => $page->where('notices.migrations', false));

    forgetLatestMigration();

    $this->get(action(HomeController::class))
        ->assertInertia(fn ($page) => $page->where('notices.migrations', false));

    event(new MigrationsEnded('up'));

    $this->get(action(HomeController::class))
        ->assertInertia(fn ($page) => $page->where('notices.migrations', true));
});
