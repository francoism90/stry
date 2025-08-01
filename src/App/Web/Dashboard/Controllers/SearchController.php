<?php

declare(strict_types=1);

namespace App\Web\Dashboard\Controllers;

use Domain\Videos\Algos\GenerateVideoCollection;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;

class SearchController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('verified'),
        ];
    }

    public function __invoke(): Response
    {
        return Inertia::render('Dashboard/DashboardIndex', [
            'recent' => Inertia::defer(fn () => GenerateVideoCollection::make(), 'sections'),
        ]);
    }
}
