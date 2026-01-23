<?php

declare(strict_types=1);

namespace App\Admin\Videos\Controllers;

use Domain\Users\Models\User;
use Domain\Videos\Actions\CreateVideosByImport;
use Domain\Videos\Models\Video;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class VideoImportController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('precognitive'),
        ];
    }

    public function __invoke(CreateVideosByImport $action): RedirectResponse
    {
        Gate::authorize('create', Video::class);

        /** @var User $user */
        $user = Auth::user();

        // Flash message based on result
        $result = $action->handle($user);

        Inertia::flash('message', $result['message']);

        return back();
    }
}
