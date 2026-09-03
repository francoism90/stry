<?php

declare(strict_types=1);

use Domain\Groups\Models\Group;
use Domain\Media\Models\Media;
use Domain\Playlists\Models\Playlist;
use Domain\Profiles\Models\Profile;
use Domain\Relates\Models\Related;
use Domain\Tags\Models\Tag;
use Domain\Users\Models\User;
use Domain\Videos\Models\Video;
use Foxws\Essentials\Configurables\AggressivePrefetching;
use Foxws\Essentials\Configurables\AutomaticallyEagerLoadRelationships;
use Foxws\Essentials\Configurables\EnforceMorphMap;
use Foxws\Essentials\Configurables\FakeSleep;
use Foxws\Essentials\Configurables\ForceHttpsScheme;
use Foxws\Essentials\Configurables\ForceSecurePassword;
use Foxws\Essentials\Configurables\ImmutableDates;
use Foxws\Essentials\Configurables\ModelShouldBeStrict;
use Foxws\Essentials\Configurables\PreventStrayRequests;
use Foxws\Essentials\Configurables\ProhibitDestructiveCommands;
use Foxws\Essentials\Configurables\ResourceWithoutWrapping;

return [

    /*
    |--------------------------------------------------------------------------
    | Configurable Features
    |--------------------------------------------------------------------------
    |
    | This array contains a list of configurable features that can be enabled
    | or disabled in your application.
    | Each feature is represented by a class that implements the Configurable interface.
    | You can add or remove features from this array as needed.
    |
    */

    'configurables' => env('ESSENTIALS_CONFIGURABLES', [
        AggressivePrefetching::class,
        AutomaticallyEagerLoadRelationships::class,
        EnforceMorphMap::class,
        FakeSleep::class,
        ForceHttpsScheme::class,
        ForceSecurePassword::class,
        ImmutableDates::class,
        ModelShouldBeStrict::class,
        PreventStrayRequests::class,
        ProhibitDestructiveCommands::class,
        ResourceWithoutWrapping::class,
    ]),

    /*
    |--------------------------------------------------------------------------
    | Model Morph Map
    |--------------------------------------------------------------------------
    |
    | This array defines the morph map for your Eloquent models.
    | The morph map allows you to customize the names used for polymorphic relationships.
    |
    */

    'morph_map' => env('ESSENTIALS_MORPH_MAP', [
        'group' => Group::class,
        'media' => Media::class,
        'playlist' => Playlist::class,
        'profile' => Profile::class,
        'related' => Related::class,
        'tag' => Tag::class,
        'user' => User::class,
        'video' => Video::class,
    ]),

];
