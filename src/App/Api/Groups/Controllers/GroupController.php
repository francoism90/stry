<?php

declare(strict_types=1);

namespace App\Api\Groups\Controllers;

use App\Api\Groups\Requests\GroupIndexRequest;
use App\Api\Groups\Resources\GroupResource;
use Domain\Groups\Models\Group;
use Domain\Groups\Scopes\GroupTypeScope;
use Foundation\Http\Controllers\Controller;
use Illuminate\Pagination\Paginator;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;

class GroupController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }


}
