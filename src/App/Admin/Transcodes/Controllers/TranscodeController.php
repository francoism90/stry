<?php

declare(strict_types=1);

namespace App\Admin\Transcodes\Controllers;

use App\Admin\Transcodes\Responses\TranscodeResourceProperty;
use App\Api\Transcodes\Requests\TranscodeIndexRequest;
use App\Api\Transcodes\Requests\TranscodeUpdateRequest;
use App\Api\Transcodes\Resources\TranscodeResource;
use Domain\Transcodes\Enums\TranscodeEncoder;
use Domain\Transcodes\Models\Transcode;
use Domain\Transcodes\Scopes\TranscodeFilterScope;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class TranscodeController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('precognitive'),
        ];
    }

    public function index(TranscodeIndexRequest $request): Response
    {
        Gate::authorize('viewAny', Transcode::class);

        // Apply filters
        $encoder = $request->safe()->input('encoder');

        // Query builder
        $query = Transcode::query()
            ->tap(new TranscodeFilterScope(encoder: $encoder))
            ->simplePaginate(16);

        return Inertia::render('Admin/Transcodes/TranscodeIndex', [
            'items' => Inertia::scroll(fn () => TranscodeResource::collection($query)),
            'encoder' => fn () => $encoder,
            'encoders' => fn () => TranscodeEncoder::options(),
        ]);
    }

    public function edit(Transcode $transcode): Response
    {
        Gate::authorize('update', $transcode);

        return Inertia::render('Admin/Transcodes/TranscodeEdit', [
            'transcode' => fn () => new TranscodeResourceProperty($transcode),
            'encoders' => fn () => TranscodeEncoder::options(),
        ]);
    }

    public function update(Transcode $transcode, TranscodeUpdateRequest $request): RedirectResponse
    {
        Gate::authorize('update', $transcode);

        // Update transcode details
        $transcode->updateOrFail($request->safe()->all());

        return Inertia::flash('message', __('The transcode has been updated.'))->back();
    }

    public function destroy(Transcode $transcode): RedirectResponse
    {
        Gate::authorize('delete', $transcode);

        // Delete the transcode
        $transcode->deleteOrFail();

        // Flash message
        return Inertia::flash('message', __('The transcode has been deleted.'))->back();
    }
}
