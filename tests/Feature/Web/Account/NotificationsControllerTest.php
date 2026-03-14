<?php

declare(strict_types=1);

use App\Api\Notifications\Controllers\MarkAllNotificationsReadController;
use App\Web\Account\Controllers\NotificationsController;
use Domain\Users\Models\User;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;

function createNotification(User $user, bool $read = false): DatabaseNotification
{
    return DatabaseNotification::create([
        'id' => (string) Str::uuid(),
        'type' => 'App\\Notifications\\TestNotification',
        'notifiable_type' => (new User)->getMorphClass(),
        'notifiable_id' => $user->getKey(),
        'data' => ['title' => 'Test', 'message' => 'A test notification.'],
        'read_at' => $read ? now() : null,
    ]);
}

// index

it('allows authenticated users to view their notifications', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(action([NotificationsController::class, 'index']));

    $response->assertSuccessful();
});

it('redirects guests from the notifications index', function () {
    $response = $this->get(action([NotificationsController::class, 'index']));

    $response->assertRedirect();
});

// update (toggle read)

it('allows a user to mark a notification as read', function () {
    $user = User::factory()->create();
    $notification = createNotification($user, false);

    $response = $this->actingAs($user)->patch(
        action([NotificationsController::class, 'update'], $notification->id)
    );

    $response->assertRedirect();
    expect($notification->fresh()->read_at)->not->toBeNull();
});

it('allows a user to mark a notification as unread', function () {
    $user = User::factory()->create();
    $notification = createNotification($user, true);

    $response = $this->actingAs($user)->patch(
        action([NotificationsController::class, 'update'], $notification->id)
    );

    $response->assertRedirect();
    expect($notification->fresh()->read_at)->toBeNull();
});

it("forbids a user from updating another user's notification", function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $notification = createNotification($owner);

    $response = $this->actingAs($other)->patch(
        action([NotificationsController::class, 'update'], $notification->id)
    );

    $response->assertNotFound();
});

// destroy

it('allows a user to delete their own notification', function () {
    $user = User::factory()->create();
    $notification = createNotification($user);

    $response = $this->actingAs($user)->delete(
        action([NotificationsController::class, 'destroy'], $notification->id)
    );

    $response->assertRedirect();
    expect(DatabaseNotification::find($notification->id))->toBeNull();
});

it("forbids a user from deleting another user's notification", function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $notification = createNotification($owner);

    $response = $this->actingAs($other)->delete(
        action([NotificationsController::class, 'destroy'], $notification->id)
    );

    $response->assertNotFound();
});

// mark all read

it('allows a user to mark all notifications as read', function () {
    $user = User::factory()->create();
    createNotification($user, false);
    createNotification($user, false);

    $response = $this->actingAs($user)->post(
        action(MarkAllNotificationsReadController::class)
    );

    $response->assertSuccessful();
    expect($user->unreadNotifications()->count())->toBe(0);
});

it('redirects guests from mark-all-read', function () {
    $response = $this->post(action(MarkAllNotificationsReadController::class));

    $response->assertRedirect();
});
