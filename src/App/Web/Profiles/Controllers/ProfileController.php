<?php

declare(strict_types=1);

namespace App\Web\Profiles\Controllers;

use App\Api\Profiles\Requests\ProfileStoreRequest;
use App\Api\Profiles\Requests\ProfileUpdateRequest;
use App\Web\Profiles\Responses\ProfileCollectionProperty;
use Domain\Profiles\Models\Profile;
use Domain\Profiles\States\Pending;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

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

    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Profile::class);

        $currentProfile = (string) $request->session()->get('profiles.current', '');

        return Inertia::render('App/Profiles/ProfileIndex', [
            'profiles' => fn (): ProfileCollectionProperty => new ProfileCollectionProperty(
                $request->user(),
                $currentProfile,
            ),
        ]);
    }

    public function store(ProfileStoreRequest $request): RedirectResponse
    {
        Gate::authorize('create', Profile::class);

        $isFirstProfile = ! $request->user()->profiles()->exists();
        $attributes = $request->safe()->all();

        $profile = $request->user()->profiles()->create([
            ...$attributes,
            'state' => Pending::class,
            'settings' => $attributes['settings'] ?? [],
            'is_primary' => $isFirstProfile || (bool) ($attributes['is_primary'] ?? false),
        ]);

        if ($profile->is_primary) {
            $request->user()
                ->profiles()
                ->whereKeyNot($profile->getKey())
                ->update(['is_primary' => false]);
        }

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

        $attributes = $request->safe()->all();

        $profile->updateOrFail($attributes);

        if ((bool) ($attributes['is_primary'] ?? false)) {
            $request->user()
                ->profiles()
                ->whereKeyNot($profile->getKey())
                ->update(['is_primary' => false]);
        }

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

        $deletedProfileKey = (string) $profile->getRouteKey();
        $deletedProfileName = (string) $profile->name;

        $profile->deleteOrFail();

        $currentProfile = (string) $request->session()->get('profiles.current', '');

        if ($currentProfile === $deletedProfileKey) {
            $nextProfile = $request->user()->profiles()->ordered()->first();

            if ($nextProfile) {
                $request->session()->put('profiles.current', $nextProfile->getRouteKey());
            } else {
                $request->session()->forget('profiles.current');
            }
        }

        Inertia::flash([
            'title' => $deletedProfileName,
            'description' => __('The profile has been deleted.'),
            'type' => 'warning',
        ]);

        return back();
    }
}
