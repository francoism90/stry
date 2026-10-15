<?php

declare(strict_types=1);

use Domain\Users\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Process;
use Modules\Web\Settings\Controllers\ApplicationRestartController;

beforeEach(fn () => Process::fake());

it('restarts the queue and octane workers for admins', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    $this->actingAs($admin)
        ->post(action(ApplicationRestartController::class))
        ->assertRedirect();

    expect(Cache::get('illuminate:queue:restart'))->not->toBeNull();

    Process::assertRan(fn ($process) => str_contains($process->command, 'artisan octane:reload'));
});

it('forbids users who cannot manage the application from restarting it', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(action(ApplicationRestartController::class))
        ->assertForbidden();

    expect(Cache::get('illuminate:queue:restart'))->toBeNull();

    Process::assertNothingRan();
});
