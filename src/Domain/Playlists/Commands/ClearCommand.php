<?php

declare(strict_types=1);

namespace Domain\Playlists\Commands;

use Domain\Playlists\Models\Playlist;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Contracts\Console\Isolatable;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'playlists:clear')]
class ClearCommand extends Command implements Isolatable
{
    use ConfirmableTrait;

    /**
     * @var string
     */
    protected $signature = 'playlists:clear {--type=clip}';

    /**
     * @var string
     */
    protected $description = 'Clear all generated playlists';

    public function handle(): ?int
    {
        if (! $this->confirmToProceed()) {
            return 1;
        }

        Playlist::query()
            ->when($this->option('type'), fn ($query, $type) => $query->type($type))
            ->lazyById(200, column: 'id')
            ->each->delete();

        $this->components->info('All generated playlists have been cleared successfully.');

        return 0;
    }
}
