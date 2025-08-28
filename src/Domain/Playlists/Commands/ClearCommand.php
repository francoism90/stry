<?php

declare(strict_types=1);

namespace Domain\Playlists\Commands;

use Domain\Playlists\Models\Playlist;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;

use function Laravel\Prompts\info;
use function Laravel\Prompts\spin;

class ClearCommand extends Command implements Isolatable
{
    /**
     * @var string
     */
    protected $signature = 'playlists:clear {--type=clip}';

    /**
     * @var string
     */
    protected $description = 'Clear generated playlists';

    public function handle(): void
    {
        $playlists = spin(
            message: 'Retrieving playlists...',
            callback: fn () => Playlist::type($this->option('type'))->lazy()
        );

        if ($playlists->isEmpty()) {
            info('No playlists found.');

            return;
        }

        $playlists->each(function (Playlist $model) {
            info("deleting playlist with type {$model->type} ({$model->getKey()})");

            $model->delete();
        });
    }
}
