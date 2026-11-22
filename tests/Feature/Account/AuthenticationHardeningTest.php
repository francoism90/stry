<?php

declare(strict_types=1);

use Domain\Users\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Modules\Web\Users\Controllers\UserController;

// Unique accounts: the throttles count in Redis, which tests don't roll back.
beforeEach(function () {
    $this->email = Str::lower(Str::random(16)).'@example.com';
    $this->user = User::factory()->create(['email' => $this->email]);
});

it('believes forwarded headers only from a proxy on a private address', function (string $remote, string $ip) {
    Route::get('/_client', fn () => ['ip' => request()->ip(), 'host' => request()->getHost()]);

    $this->withServerVariables(['REMOTE_ADDR' => $remote])
        ->getJson('http://stry.example.com/_client', [
            'X-Forwarded-For' => '198.51.100.7',
            'X-Forwarded-Host' => 'attacker.example',
        ])
        ->assertExactJson(['ip' => $ip, 'host' => 'stry.example.com']);
})->with([
    'the proxy' => ['10.89.0.5', '198.51.100.7'],
    'a client' => ['203.0.113.9', '203.0.113.9'],
]);

it('limits logins per account when the address keeps changing', function () {
    foreach (range(1, 20) as $attempt) {
        $this->post(route('login.store'), ['email' => $this->email, 'password' => 'wrong-password'], ['X-Forwarded-For' => "198.51.100.{$attempt}"])
            ->assertRedirect();
    }

    $this->post(route('login.store'), ['email' => $this->email, 'password' => 'wrong-password'], ['X-Forwarded-For' => '198.51.100.200'])
        ->assertTooManyRequests();
});

it('mails a reset link on the configured host, whatever the request names', function () {
    config(['app.url' => 'https://stry.example.com']);

    $this->post('http://attacker.example/forgot-password', ['email' => $this->email])->assertRedirect();

    Notification::assertSentTo(
        $this->user,
        fn (ResetPassword $notification): bool => str_starts_with($notification->toMail($this->user)->actionUrl, "https://stry.example.com/reset-password/{$notification->token}?"),
    );
});

it('signs a session out when the password changes elsewhere', function () {
    $this->post(route('login.store'), ['email' => $this->email, 'password' => 'password'])->assertRedirect();
    $this->get(route('notifications.index'))->assertSuccessful();

    $this->user->forceFill(['password' => Hash::make('An0ther-Secret-Passw0rd!')])->save();
    app('auth')->forgetGuards();

    $this->get(route('notifications.index'))->assertRedirect(route('login'));
});

it('keeps a session that changes its own password', function () {
    $this->post(route('login.store'), ['email' => $this->email, 'password' => 'password'])->assertRedirect();
    $this->get(route('notifications.index'))->assertSuccessful();

    $this->put(route('user-password.update'), [
        'current_password' => 'password',
        'password' => 'An0ther-Secret-Passw0rd!',
        'password_confirmation' => 'An0ther-Secret-Passw0rd!',
    ])->assertSessionHasNoErrors();
    app('auth')->forgetGuards();

    $this->get(route('notifications.index'))->assertSuccessful();
});

it('keeps a session that alternates between the app and its API', function () {
    $this->post(route('login.store'), ['email' => $this->email, 'password' => 'password'])->assertRedirect();

    foreach ([route('notifications.index'), route('api.home'), route('notifications.index')] as $url) {
        config(['auth.defaults.guard' => 'web']);
        app('auth')->forgetGuards();

        $this->get($url, ['Referer' => config('app.url')])->assertSuccessful();
    }

    $this->assertAuthenticatedAs($this->user);
});

it('does not let the profile form set the password or the verification', function () {
    $this->user->forceFill(['email_verified_at' => null])->save();

    $this->actingAs($this->user)
        ->put(route('user-profile-information.update'), [
            'name' => 'New Name',
            'email' => $this->email,
            'password' => 'An0ther-Secret-Passw0rd!',
            'email_verified_at' => '2026-01-01 00:00:00',
        ])
        ->assertRedirect();

    expect(Hash::check('password', $this->user->refresh()->password))->toBeTrue()
        ->and($this->user->email_verified_at)->toBeNull();
});

it('needs the current password to change your own email', function (string $route, ?string $password, bool $changes) {
    $this->user->assignRole('super-admin');
    $email = Str::lower(Str::random(16)).'@example.com';
    $url = $route === 'profile' ? route('user-profile-information.update') : action([UserController::class, 'update'], $this->user);

    $response = $this->actingAs($this->user)->put($url, ['name' => 'Name', 'email' => $email, 'current_password' => $password]);

    $changes
        ? $response->assertSessionHasNoErrors()
        : $response->assertSessionHasErrorsIn('updateProfileInformation', 'current_password');

    expect($this->user->refresh()->email)->toBe($changes ? $email : $this->email);
})->with(['profile', 'users'])->with([
    'no password' => [null, false],
    'a wrong password' => ['wrong-password', false],
    'the password' => ['password', true],
]);

it('limits guesses at the current password', function () {
    $this->actingAs($this->user);
    $data = ['name' => 'Name', 'email' => 'new-'.$this->email, 'current_password' => 'wrong-password'];

    foreach (range(1, 6) as $attempt) {
        $this->put(route('user-profile-information.update'), $data)->assertRedirect();
    }

    $this->put(route('user-profile-information.update'), $data)->assertTooManyRequests();
});

it('keeps the secrets of the storage disks out of the session', function () {
    $this->user->assignRole('super-admin');

    $this->actingAs($this->user)
        ->put('/actions/settings/storage', ['disks' => [['name' => 'archive', 'secret' => 'hunter2']]])
        ->assertSessionHasErrors();

    expect(session()->getOldInput())->not->toHaveKey('disks');
});
