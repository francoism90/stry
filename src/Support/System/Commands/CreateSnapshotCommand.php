<?php

declare(strict_types=1);

namespace Support\System\Commands;

use Domain\Shared\Actions\CreateDatabaseSnapshot;
use Illuminate\Console\Command;
use Spatie\DbSnapshots\Helpers\Format;

use function Laravel\Prompts\info;

class CreateSnapshotCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'app:snapshot';

    /**
     * @var string
     */
    protected $description = 'Create a database snapshot on the exports disk and delete the oldest ones';

    public function handle(CreateDatabaseSnapshot $createDatabaseSnapshot): void
    {
        $snapshot = $createDatabaseSnapshot->handle();

        info(sprintf('Snapshot %s created (%s).', $snapshot->fileName, Format::humanReadableSize($snapshot->size())));
    }
}
