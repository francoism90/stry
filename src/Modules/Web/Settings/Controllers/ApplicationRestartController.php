<?php

declare(strict_types=1);

namespace Modules\Web\Settings\Controllers;

use Domain\Shared\Actions\RestartApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;

class ApplicationRestartController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('verified'),
        ];
    }

    public function __invoke(RestartApplication $restartApplication): RedirectResponse
    {
        Gate::authorize('manage-application-settings');

        $restartApplication->handle();

        toast(title: __('Restarting'), description: __('The workers restart in a few seconds. Reload the page if this notice stays.'), type: 'info');

        return back();
    }
}
