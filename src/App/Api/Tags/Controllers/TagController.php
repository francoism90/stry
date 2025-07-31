<?php

declare(strict_types=1);

namespace App\Api\Tags\Controllers;

use App\Api\Tags\Requests\TagIndexRequest;
use App\Api\Tags\Resources\TagResource;
use Domain\Tags\Algos\GetMatchingTagCollection;
use Domain\Tags\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class TagController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function index(TagIndexRequest $request): ResourceCollection
    {
        return GetMatchingTagCollection::make($request->input('query'));
    }

    public function store(Request $request)
    {
        //
    }

    public function show(string $id)
    {
        //
    }

    public function update(Tag $tag): TagResource
    {
        // $tag = app(UpdateTagDetails::class)->handle($tag, $request->validated());

        return TagResource::make($tag);
    }

    public function destroy(string $id)
    {
        //
    }
}
