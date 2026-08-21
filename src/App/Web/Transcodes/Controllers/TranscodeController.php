<?php

declare(strict_types=1);

namespace App\Web\Transcodes\Controllers;

use App\Api\Transcodes\Requests\TranscodeUpdateRequest;
use App\Api\Transcodes\Resources\TranscodeResource;
use Domain\Transcodes\Enums\TranscodeScope;
use Domain\Transcodes\Models\Transcode;
use Domain\Transcodes\QueryBuilders\TranscodeQueryBuilder;
use Foundation\Http\Properties\ScoutBuilderProperties;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\LaravelOptions\Options;

class TranscodeController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Transcode::class);

        // Requested scope
        $scope = TranscodeScope::tryFrom((string) $request->input('filter.scope'));

        // Fetch all transcodes
        $transcodes = Transcode::query()
            ->with('transcodable')
            ->when($scope, fn (TranscodeQueryBuilder $query, TranscodeScope $scope) => match ($scope) {
                TranscodeScope::Pending => $query->pending(),
                TranscodeScope::Processing => $query->processing(),
                TranscodeScope::Completed => $query->completed(),
                TranscodeScope::Failed => $query->failed(),
                TranscodeScope::All => null,
            })
            ->latest()
            ->simplePaginate(perPage: 16);

        return Inertia::render('Transcodes/TranscodeIndex', [
            'items' => Inertia::scroll(fn () => TranscodeResource::collection($transcodes)),
            'scopes' => fn () => Options::forEnum(TranscodeScope::class),
            new ScoutBuilderProperties('transcodes'),
        ]);
    }

    public function update(Transcode $transcode, TranscodeUpdateRequest $request): RedirectResponse
    {
        Gate::authorize('update', $transcode);

        // Update transcode details
        $transcode->updateOrFail($request->safe()->all());

        // Notify the user
        Inertia::flash([
            'title' => (string) $transcode->file_name,
            'description' => __('The transcode has been updated.'),
            'type' => 'success',
        ]);

        return back();
    }

    public function destroy(Transcode $transcode): RedirectResponse
    {
        Gate::authorize('delete', $transcode);

        // Delete the transcode
        $transcode->deleteOrFail();

        // Notify the user
        Inertia::flash([
            'title' => (string) $transcode->file_name,
            'description' => __('The transcode has been deleted.'),
            'type' => 'warning',
        ]);

        return back();
    }
}
