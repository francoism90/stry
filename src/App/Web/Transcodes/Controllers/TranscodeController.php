<?php

declare(strict_types=1);

namespace App\Web\Transcodes\Controllers;

use App\Api\Transcodes\Requests\TranscodeUpdateRequest;
use App\Api\Transcodes\Resources\TranscodeResource;
use Domain\Transcodes\Enums\TranscodeScope;
use Domain\Transcodes\Enums\TranscodeSorter;
use Domain\Transcodes\Filters\TranscodeScopeFilter;
use Domain\Transcodes\Models\Transcode;
use Domain\Transcodes\QueryBuilders\TranscodeQueryBuilder;
use Foundation\Http\Properties\ScoutBuilderProperties;
use Foxws\ScoutBuilder\AllowedFilter;
use Foxws\ScoutBuilder\AllowedSort;
use Foxws\ScoutBuilder\ScoutBuilder;
use Illuminate\Http\RedirectResponse;
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

    public function index(): Response
    {
        Gate::authorize('viewAny', Transcode::class);

        // Relevant sort options
        $defaultSort = AllowedSort::latest('newest', 'created_at');

        // Scout builder
        $scout = ScoutBuilder::for(Transcode::class)
            ->query(fn (TranscodeQueryBuilder $query) => $query->with('transcodable'))
            ->allowedFilters(
                AllowedFilter::custom('scope', new TranscodeScopeFilter),
            )
            ->allowedSorts(
                $defaultSort,
                AllowedSort::oldest('oldest', 'created_at'),
                AllowedSort::field('updated', 'updated_at')->defaultDescending(),
            )
            ->defaultSort($defaultSort)
            ->jsonSimplePaginate(defaultSize: 16);

        return Inertia::render('Transcodes/TranscodeIndex', [
            'items' => Inertia::scroll(fn () => TranscodeResource::collection($scout)),
            'scopes' => fn () => Options::forEnum(TranscodeScope::class),
            'sorters' => fn () => Options::forEnum(TranscodeSorter::class),
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
