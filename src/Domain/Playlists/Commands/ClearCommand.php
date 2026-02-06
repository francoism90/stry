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
    protected $signature = 'playlists:clear';

    /**
     * @var string
     */
    protected $description = 'Force delete generated playlists';

    public function handle(): void
    {
        $playlists = spin(
            message: 'Retrieving playlists...',
            callback: fn () => Playlist::lazy(),
        );

        if ($playlists->isEmpty()) {
            info('No playlists found.');

            return;
        }

        table(
            headers: ['ID', 'State'],
            rows: $playlists->map(fn (Playlist $playlist) => [
                (string) $playlist->getKey(),
                (string) $playlist->state,
            ])->all(),
        );

        if (confirm('Are you sure you want to delete these playlists?')) {
            $playlists->each(function (Playlist $playlist) {
                info("deleting playlist ({$playlist->getKey()})");

                $playlist->delete();
            });
        }
    }
}
