<?php

declare(strict_types=1);

namespace App\Api\Authentication\Controllers;

use Foundation\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class HomeController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('cache_response:600'),
        ];
    }

    public function __invoke(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => __('Welcome to the API.'),
        ]);
    }
}
