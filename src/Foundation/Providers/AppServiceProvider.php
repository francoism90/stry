<?php

declare(strict_types=1);

namespace Foundation\Providers;

use Domain\Groups\Models\Group;
use Domain\Media\Models\Media;
use Domain\Playlists\Models\Playlist;
use Domain\Relates\Models\Related;
use Domain\Shared\Contracts\Enumerable;
use Domain\Tags\Models\Tag;
use Domain\Users\Models\User;
use Domain\Videos\Models\Video;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerTelescope();
    }

    public function boot(): void
    {
        $this->configureUrls();
        $this->configureMacros();
        $this->configureModels();
        $this->configureMorphMap();
        $this->configureCommands();
        $this->configureJsonResource();
    }

    protected function registerTelescope(): void
    {
        if ($this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    protected function configureUrls(): void
    {
        URL::forceHttps();
    }

    protected function configureModels(): void
    {
        Model::automaticallyEagerLoadRelationships();
        Model::shouldBeStrict();
    }

    protected function configureMorphMap(): void
    {
        Relation::enforceMorphMap([
            'group' => Group::class,
            'media' => Media::class,
            'related' => Related::class,
            'tag' => Tag::class,
            'playlist' => Playlist::class,
            'user' => User::class,
            'video' => Video::class,
        ]);
    }

    protected function configureCommands(): void
    {
        DB::prohibitDestructiveCommands(
            $this->app->environment('production')
        );
    }

    protected function configureJsonResource(): void
    {
        JsonResource::withoutWrapping();
    }

    protected function configureMacros(): void
    {
        Collection::macro('forEnum', function () {
            /** @var Collection $this */
            return $this->map(fn (Enumerable $item) => [
                'value' => $item->value,
                'label' => $item->label(),
            ]);
        });
    }
}
