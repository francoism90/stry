<?php

declare(strict_types=1);

namespace App\Api\Authentication\Controllers;

use Foundation\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Spatie\ResponseCache\Attributes\Cache;

#[Cache(lifetime: 10 * 60, tags: ['api'])]
class HomeController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'message' => 'Welcome to the API',
        ]);
    }
}
