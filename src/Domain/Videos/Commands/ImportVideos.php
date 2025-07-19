<?php

declare(strict_types=1);

namespace Domain\Videos\Commands;

use Domain\Users\Models\User;
use Domain\Videos\Jobs\ImportVideo;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

use function Laravel\Prompts\info;
use function Laravel\Prompts\progress;
use function Laravel\Prompts\search;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\table;

class ImportVideos extends Command implements Isolatable
{
    /**
     * @var string
     */
    protected $signature = 'videos:import {--disk=import}';

    /**
     * @var string
     */
    protected $description = 'Import videos to the database';

    public function handle(): void
    {
        $files = spin(
            message: 'Retrieving files...',
            callback: fn () => $this->getCollection()
        );

        if ($files->isEmpty()) {
            info('No video files found in the import directory.');

            return;
        }

        table(
            headers: ['Filename', 'Filesize'],
            rows: collect($files->getIterator())->map(fn (string $path) => [
                Str::limit($path),
                Number::fileSize($this->getFileSystem()->size($path)),
            ])->all()
        );

        $user = search(
            label: 'Select user to assign videos to',
            validate: ['id' => 'required|exists:users,id'],
            placeholder: 'e.g. administrator@example.com',
            options: fn (string $value) => strlen($value) > 0
                ? User::whereLike('email', "%{$value}%")->pluck('email', 'id')->all()
                : []
        );

        $user = User::findOrFail($user);

        progress(
            label: 'Importing videos',
            steps: $files->getIterator(),
            callback: function (string $path, $progress) use ($user) {
                $progress->label("Importing {$path}");

                return ImportVideo::dispatch($user, $this->option('disk'), $path);
            }
        );
    }

    protected function getCollection(): Collection
    {
        return collect($this->getFileSystem()->allFiles())
            ->filter(fn (string $path) => rescue(fn () => str_starts_with($this->getFileSystem()->mimeType($path), 'video/'), report: false))
            ->sort();
    }

    protected function getFileSystem(): FilesystemAdapter
    {
        return Storage::disk($this->option('disk'));
    }
}
