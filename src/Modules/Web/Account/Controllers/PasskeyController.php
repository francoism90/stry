<?php

declare(strict_types=1);

namespace Modules\Web\Account\Controllers;

use Domain\Users\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Modules\Api\Users\Resources\PasskeyResource;

class PasskeyController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('verified'),
        ];
    }

    public function index(#[CurrentUser] User $user): AnonymousResourceCollection
    {
        Gate::authorize('update', $user);

        return PasskeyResource::collection($user->passkeys()->latest()->get());
    }
}
