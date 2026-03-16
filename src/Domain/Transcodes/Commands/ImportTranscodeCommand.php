<?php

declare(strict_types=1);

namespace Domain\Transcodes\Commands;

use Domain\Transcodes\Actions\ImportTranscode;
use Domain\Transcodes\Models\Transcode;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Support\Number;

use function Laravel\Prompts\info;
use function Laravel\Prompts\progress;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\table;

class ImportTranscodeCommand extends Command implements Isolatable
{
    /**
     * @var string
     */
    protected $signature = 'transcodes:import';

    /**
     * @var string
     */
    protected $description = 'Import verified transcodes to their associated models';

    public function handle(): void
    {
        $transcodes = spin(
            message: 'Retrieving transcodes...',
            callback: fn () => Transcode::query()
                ->with('transcodable')
                ->completed()
                ->ordered()
                ->get(),
        );

        if ($transcodes->isEmpty()) {
            info('No completed transcodes found to import.');

            return;
        }

        table(
            headers: ['ID', 'Model', 'Encoder', 'File Size', 'Transcoded At'],
            rows: $transcodes->map(fn (Transcode $transcode) => [
                (string) $transcode->getRouteKey(),
                (string) $transcode->transcodable?->name,
                (string) $transcode->encoder->value,
                Number::fileSize($transcode->getFileSize()),
                (string) $transcode->transcoded_at,
            ])->all(),
        );

        progress(
            label: 'Importing transcodes',
            steps: $transcodes->getIterator(),
            callback: function (Transcode $transcode, $progress) {
                $progress->label("Importing transcode ({$transcode->getRouteKey()})...");

                app(ImportTranscode::class)->handle($transcode);
            },
        );

        info('Transcodes imported successfully.');
    }
}
