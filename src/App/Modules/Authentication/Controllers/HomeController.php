<?php

declare(strict_types=1);

namespace App\Modules\Authentication\Controllers;

use Foundation\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Spatie\ResponseCache\Attributes\Cache;

#[Cache(lifetime: 60 * 60, tags: ['api'])]
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
