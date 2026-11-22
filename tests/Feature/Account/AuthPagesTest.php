<?php

declare(strict_types=1);

use Domain\Users\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;

// Unique accounts: the throttles count in Redis, which tests don't roll back.
beforeEach(function () {
    $this->email = Str::lower(Str::random(16)).'@example.com';
    $this->user = User::factory()->create(['email' => $this->email]);
});

it('links to the forgotten password page from the login', function () {
    $this->get(route('login'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Auth/AuthLogin')
            ->where('forgotPasswordUrl', '/forgot-password'));

    $this->get(route('password.request'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Auth/AuthForgotPassword')
            ->where('action', '/forgot-password')
            ->where('loginUrl', '/login'));
});

it('resets the password through the mailed link', function () {
    $this->post(route('password.email'), ['email' => $this->email])->assertRedirect();

    $notification = Notification::sent($this->user, ResetPassword::class)->sole();
    $link = parse_url($notification->toMail($this->user)->actionUrl);

    $this->get("{$link['path']}?{$link['query']}")
        ->assertInertia(fn (Assert $page) => $page
            ->component('Auth/AuthResetPassword')
            ->where('action', '/reset-password')
            ->where('token', $notification->token)
            ->where('email', $this->email));

    $this->post(route('password.update'), [
        'token' => $notification->token,
        'email' => $this->email,
        'password' => 'An0ther-Secret-Passw0rd!',
        'password_confirmation' => 'An0ther-Secret-Passw0rd!',
    ])->assertRedirect(route('login'))->assertSessionHas('status');

    expect(Hash::check('An0ther-Secret-Passw0rd!', $this->user->refresh()->password))->toBeTrue();
});

it('asks an unverified user to verify the email, and verifies it through the mailed link', function () {
    $this->user->forceFill(['email_verified_at' => null])->save();

    $this->actingAs($this->user)
        ->get(route('notifications.index'))
        ->assertRedirect(route('verification.notice'));

    $this->get(route('verification.notice'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Auth/AuthVerifyEmail')
            ->where('action', '/email/verification-notification'));

    $this->post(route('verification.send'))->assertSessionHas('status', 'verification-link-sent');

    $notification = Notification::sent($this->user, VerifyEmail::class)->sole();

    $this->get($notification->toMail($this->user)->actionUrl)->assertRedirect();

    expect($this->user->refresh()->hasVerifiedEmail())->toBeTrue();
});

it('shows the password confirmation page', function () {
    $this->actingAs($this->user)
        ->get(route('password.confirm'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Auth/AuthConfirmPassword')
            ->where('action', '/user/confirm-password'));
});

it('refuses a new password that is not confirmed', function (array $input) {
    $this->post(route('password.update'), [
        'token' => Password::createToken($this->user),
        'email' => $this->email,
        ...$input,
    ])->assertSessionHasErrors('password');

    $this->actingAs($this->user)
        ->put(route('user-password.update'), ['current_password' => 'password', ...$input])
        ->assertSessionHasErrorsIn('updatePassword', 'password');

    expect(Hash::check('password', $this->user->refresh()->password))->toBeTrue();
})->with([
    'another confirmation' => [['password' => 'An0ther-Secret-Passw0rd!', 'password_confirmation' => 'A-Typ0-In-The-Passw0rd!']],
    'no confirmation' => [['password' => 'An0ther-Secret-Passw0rd!']],
    'no password' => [[]],
]);
