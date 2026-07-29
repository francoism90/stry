<?php

declare(strict_types=1);

namespace App\Modules\Profiles\Controllers;

use App\Modules\Profiles\Requests\ProfileIndexRequest;
use App\Modules\Profiles\Requests\ProfileStoreRequest;
use App\Modules\Profiles\Requests\ProfileUpdateRequest;
use App\Modules\Profiles\Resources\ProfileResource;
use App\Modules\Profiles\Responses\ProfileResourceProperty;
use Domain\Profiles\Actions\CreateNewProfile;
use Domain\Profiles\Actions\UpdateProfileDetails;
use Domain\Profiles\Enums\ProfileSorter;
use Domain\Profiles\Models\Profile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\LaravelOptions\Options;

class ProfileController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function index(ProfileIndexRequest $request): Response
    {
        Gate::authorize('viewAny', Profile::class);

        // Apply filters
        $sort = $request->safe()->input('sort');

        // Query builder
        $query = $request->user()
            ->profiles()
            ->ordered(order: $sort)
            ->simplePaginate(perPage: 24);

        return Inertia::render('App/Profiles/ProfileIndex', [
            'profile' => fn () => new ProfileResourceProperty,
            'items' => Inertia::scroll(fn () => ProfileResource::collection($query)),
            'sort' => fn () => $sort,
            'sorters' => fn () => Options::forEnum(ProfileSorter::class),
        ]);
    }

    public function store(ProfileStoreRequest $request): RedirectResponse
    {
        Gate::authorize('create', Profile::class);

        $profile = app(CreateNewProfile::class)->handle(
            user: $request->user(),
            attributes: $request->safe()->all(),
        );

        Inertia::flash([
            'title' => (string) $profile->name,
            'description' => __('The profile has been created.'),
            'type' => 'success',
        ]);

        return back();
    }

    public function update(ProfileUpdateRequest $request, Profile $profile): RedirectResponse
    {
        Gate::authorize('update', $profile);

        app(UpdateProfileDetails::class)->handle(
            profile: $profile,
            attributes: $request->safe()->all(),
        );

        Inertia::flash([
            'title' => (string) $profile->name,
            'description' => __('The profile has been updated.'),
            'type' => 'success',
        ]);

        return back();
    }

    public function destroy(Request $request, Profile $profile): RedirectResponse
    {
        Gate::authorize('delete', $profile);

        // Get the profile's key and name before deletion for flash messaging and session management.
        $profileKey = (string) $profile->getRouteKey();
        $profileName = (string) $profile->name;

        // Delete the profile.
        $profile->deleteOrFail();

        // Check if the deleted profile was the currently selected profile in the session.
        $currentProfile = (string) $request->session()->get('profiles.current', '');

        if ($currentProfile === $profileKey) {
            $request->session()->forget('profiles.current');
        }

        Inertia::flash([
            'title' => $profileName,
            'description' => __('The profile has been deleted.'),
            'type' => 'warning',
        ]);

        return back();
    }
}
