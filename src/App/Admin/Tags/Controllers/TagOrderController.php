<?php

declare(strict_types=1);

namespace App\Admin\Tags\Controllers;

use Domain\Tags\Actions\SetTagsOrder;
use Domain\Tags\Models\Tag;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class TagOrderController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('precognitive'),
        ];
    }

    public function __invoke(): RedirectResponse
    {
        Gate::authorize('create', Tag::class);

        // Set the order
        app(SetTagsOrder::class)->handle();

        // Flash message
        Inertia::flash('message', __('Tags order has been updated successfully.'));

        return back();
    }
}
