<?php

declare(strict_types=1);

namespace App\Api\Tags\Controllers;

use Domain\Tags\Actions\SetTagsOrder;
use Domain\Tags\Models\Tag;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class TagOrderController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('verified'),
        ];
    }

    public function __invoke(Request $request): RedirectResponse
    {
        Gate::authorize('create', Tag::class);

        app(SetTagsOrder::class)->handle();

        Inertia::flash([
            'title' => __('Tags reordered'),
            'description' => __('The display order has been updated.'),
        ]);

        return back();
    }
}
