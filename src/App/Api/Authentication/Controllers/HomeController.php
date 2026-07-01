<?php

declare(strict_types=1);

namespace App\Api\Authentication\Controllers;

use Foundation\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

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
