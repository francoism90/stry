<?php

declare(strict_types=1);

namespace Domain\Playlists\Commands;

use Domain\Playlists\Models\Playlist;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\table;

class ClearCommand extends Command implements Isolatable
{
    /**
     * @var string
     */
    protected $signature = 'playlists:clear {--type=clip}';

    /**
     * @var string
     */
    protected $description = 'Clear all generated playlists';

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

        table(
            headers: ['ID', 'Disk', 'Type', 'Model Type', 'Model ID'],
            rows: $playlists->map(fn (Playlist $playlist) => [
                $playlist->getKey(),
                $playlist->disk,
                $playlist->type,
                $playlist->playlistable_type,
                $playlist->playlistable_id,
            ])->all()
        );

        if (confirm('Are you sure you want to delete these playlists?')) {
            $playlists->each(function (Playlist $model) {
                info("deleting {$model->type} ({$model->getKey()})");

                $model->delete();
            });
        }
    }
}
