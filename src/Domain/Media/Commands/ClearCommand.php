<?php

declare(strict_types=1);

namespace Domain\Media\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Support\Facades\Storage;

use function Laravel\Prompts\info;

class ClearCommand extends Command implements Isolatable
{
    use ConfirmableTrait;

    /**
     * @var string
     */
    protected $signature = 'transcodes:clear';

    /**
     * @var string
     */
    protected $description = 'Clear all temporary cache files';

    public function handle(): ?int
    {
        if (! $this->confirmToProceed()) {
            return 1;
        }

        foreach ($this->getPaths() as $path) {
            if (! Storage::disk('transcodes')->directoryExists($path)) {
                continue;
            }

            info("Deleting directory {$path}...");

            Storage::disk('transcodes')->deleteDirectory($path);
        }

        return 0;
    }

    protected function getPaths(): array
    {
        return [
            'captions',
            'frames',
            'segments',
        ];
    }
}
