<?php

declare(strict_types=1);

namespace Foundation\Providers;

use Domain\Groups\Models\Group;
use Domain\Media\Models\Media;
use Domain\Playlists\Models\Playlist;
use Domain\Profiles\Models\Profile;
use Domain\Profiles\Support\CurrentProfileContext;
use Domain\Relates\Models\Related;
use Domain\Tags\Models\Tag;
use Domain\Users\Models\User;
use Domain\Videos\Models\Video;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Support\Filesystem\FilesystemManager;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerFilesystem();
        $this->registerTelescope();
        $this->registerProfileContext();
    }

    public function boot(): void
    {
        $this->configureUrls();
        $this->configureModels();
        $this->configureMorphMap();
        $this->configureCommands();
        $this->configureJsonResource();
    }

    protected function registerFilesystem(): void
    {
        $this->app->singleton('filesystem', fn ($app) => new FilesystemManager($app));
    }

    protected function registerTelescope(): void
    {
        if ($this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    protected function registerProfileContext(): void
    {
        $this->app->scoped(CurrentProfileContext::class, fn (): CurrentProfileContext => new CurrentProfileContext);
    }

    protected function configureUrls(): void
    {
        URL::forceHttps();
    }

    protected function configureModels(): void
    {
        Model::automaticallyEagerLoadRelationships();
        Model::shouldBeStrict(! $this->app->environment('production'));
    }

    protected function configureMorphMap(): void
    {
        Relation::enforceMorphMap([
            'group' => Group::class,
            'media' => Media::class,
            'playlist' => Playlist::class,
            'profile' => Profile::class,
            'related' => Related::class,
            'tag' => Tag::class,
            'user' => User::class,
            'video' => Video::class,
        ]);
    }

    protected function configureCommands(): void
    {
        DB::prohibitDestructiveCommands(
            $this->app->environment('production'),
        );
    }

    protected function configureJsonResource(): void
    {
        JsonResource::withoutWrapping();
    }
}
