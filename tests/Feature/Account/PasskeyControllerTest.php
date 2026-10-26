<?php

declare(strict_types=1);

use Domain\Users\Models\User;
use Illuminate\Support\Str;
use Laravel\Passkeys\Passkey;
use Modules\Web\Account\Controllers\PasskeyController;

function createPasskey(User $user, string $name = 'MacBook'): Passkey
{
    return $user->passkeys()->create([
        'name' => $name,
        'credential_id' => Str::random(32),
        'credential' => ['type' => 'public-key'],
    ]);
}

// index

it('lists only the passkeys of the authenticated user', function () {
    $user = User::factory()->create();
    $passkey = createPasskey($user);
    createPasskey(User::factory()->create(), 'Other');

    $response = $this->actingAs($user)->getJson(action([PasskeyController::class, 'index']));

    $response->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.id', $passkey->id)
        ->assertJsonPath('0.name', 'MacBook');
});

it('redirects guests away from the passkey list', function () {
    $response = $this->get(action([PasskeyController::class, 'index']));

    $response->assertRedirect(route('login'));
});

// registration

it('requires a confirmed password before registering a passkey', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson(route('passkey.registration-options'));

    $response->assertStatus(423);
});

it('returns registration options once the password is confirmed', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => now()->unix()])
        ->getJson(route('passkey.registration-options'));

    $response->assertOk()->assertJsonStructure(['options' => ['challenge', 'rp', 'user']]);
});

// deletion

it('deletes a passkey of the authenticated user', function () {
    $user = User::factory()->create();
    $passkey = createPasskey($user);

    $response = $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => now()->unix()])
        ->deleteJson(route('passkey.destroy', $passkey));

    $response->assertOk();

    $this->assertModelMissing($passkey);
});

it('does not delete a passkey of another user', function () {
    $user = User::factory()->create();
    $passkey = createPasskey(User::factory()->create());

    $response = $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => now()->unix()])
        ->deleteJson(route('passkey.destroy', $passkey));

    $response->assertForbidden();

    $this->assertModelExists($passkey);
});

// login

it('returns login options to guests', function () {
    $this->withServerVariables(['REMOTE_ADDR' => fake()->unique()->ipv4()]);

    $response = $this->getJson(route('passkey.login-options'));

    $response->assertOk()->assertJsonStructure(['options' => ['challenge']]);
});

it('throttles passkey login attempts', function () {
    $this->withServerVariables(['REMOTE_ADDR' => fake()->unique()->ipv4()]);

    foreach (range(1, 10) as $attempt) {
        $this->getJson(route('passkey.login-options'))->assertOk();
    }

    $this->getJson(route('passkey.login-options'))->assertTooManyRequests();
});
