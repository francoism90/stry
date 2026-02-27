<?php

declare(strict_types=1);

namespace Domain\Videos\Commands;

use Domain\Users\Models\User;
use Domain\Videos\Actions\FetchImportableVideos;
use Domain\Videos\DataObjects\VideoFile;
use Domain\Videos\Jobs\CreateVideo;
use Domain\Videos\Models\Video;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Support\Collection;
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

    public function handle(): void
    {
        // Retrieve the collection of video files from the specified disk
        $files = spin(
            message: 'Retrieving files...',
            callback: fn () => app(FetchImportableVideos::class)->handle(
                $this->option('disk') ?: Video::getImportDisk()
            ),
        );

        if ($files->isEmpty()) {
            info('No video files found in the import directory.');

            return;
        }

        table(
            headers: ['Filename', 'Filesize'],
            rows: Collection::make($files)->map(fn (VideoFile $file) => [
                Str::limit($file->name, 50),
                Number::fileSize($file->size),
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

        // Fetch the user model based on the selected user ID
        $user = User::findOrFail($user);

        // Process each video file and dispatch an import job for each one
        progress(
            label: 'Importing videos',
            steps: $files->getIterator(),
            callback: function (VideoFile $file, $progress) use ($user) {
                $progress->label("Importing {$file->path}...");

                return CreateVideo::dispatch($user, $file);
            },
        );
    }
}
