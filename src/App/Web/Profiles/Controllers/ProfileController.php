<?php

declare(strict_types=1);

namespace App\Web\Profiles\Controllers;

use App\Api\Profiles\Requests\ProfileIndexRequest;
use App\Api\Profiles\Requests\ProfileStoreRequest;
use App\Api\Profiles\Requests\ProfileUpdateRequest;
use App\Api\Profiles\Resources\ProfileResource;
use App\Web\Profiles\Responses\ProfileResourceProperty;
use Domain\Profiles\Actions\CreateNewProfile;
use Domain\Profiles\Actions\UpdateProfileDetails;
use Domain\Profiles\Enums\ProfileOrder;
use Domain\Profiles\Models\Profile;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\LaravelOptions\Options;

class ProfileController extends Controller implements HasMiddleware
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

        $order = $request->safe()->input('order');

        // Query builder
        $query = $request->user()
            ->profiles()
            ->ordered(order: $order)
            ->simplePaginate(perPage: 24);

        return Inertia::render('App/Profiles/ProfileIndex', [
            'profile' => fn () => new ProfileResourceProperty,
            'items' => Inertia::scroll(fn () => ProfileResource::collection($query)),
            'order' => fn () => $order,
            'orders' => fn () => Options::forEnum(ProfileOrder::class),
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
