<?php

declare(strict_types=1);

namespace Domain\Videos\Commands;

use Domain\Users\Models\User;
use Domain\Videos\Actions\CreateVideosByImport;
use Domain\Videos\Jobs\ImportVideo;
use Domain\Videos\Models\Video;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

use function Laravel\Prompts\info;
use function Laravel\Prompts\progress;
use function Laravel\Prompts\search;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\table;

class ImportCommand extends Command implements Isolatable
{
    /**
     * @var string
     */
    protected $signature = 'videos:import {--disk=}';

    /**
     * @var string
     */
    protected $description = 'Import videos to the database';

    public function handle(CreateVideosByImport $action): void
    {
        // Determine the disk to use for importing videos
        $disk = $this->option('disk') ?: Video::getImportDisk();

        // Retrieve the collection of video files from the specified disk
        $files = spin(
            message: 'Retrieving files...',
            callback: fn () => $action->getCollection($disk),
        );

        if ($files->isEmpty()) {
            info('No video files found in the import directory.');

            return;
        }

        table(
            headers: ['Filename', 'Filesize'],
            rows: collect($files->getIterator())->map(fn (string $path) => [
                Str::limit($path),
                Number::fileSize($this->getFileSystem($disk)->size($path)),
            ])->all(),
        );

        // Prompt the user to select a user to assign the imported videos to
        $user = search(
            label: 'Select user to assign videos to',
            validate: ['id' => 'required|exists:users,id'],
            placeholder: 'e.g. administrator@example.com',
            options: fn (string $value) => strlen($value) > 0
                ? User::whereLike('email', "%{$value}%")->pluck('email', 'id')->all()
                : User::limit(10)->pluck('email', 'id')->all(),
        );

        $user = User::findOrFail($user);

        // Process each video file and dispatch an import job for each one
        progress(
            label: 'Importing videos',
            steps: $files->getIterator(),
            callback: function (string $path, $progress) use ($user, $disk) {
                $progress->label("Importing {$path}");

                return ImportVideo::dispatch($user, $disk, $path);
            },
        );
    }

    protected function getFileSystem(string $disk): FilesystemAdapter
    {
        return Storage::disk($disk);
    }
}
