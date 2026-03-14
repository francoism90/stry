<?php

declare(strict_types=1);

namespace Domain\Transcodes\Commands;

use Domain\Transcodes\Models\Transcode;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\table;

class ClearTranscodeCommand extends Command implements Isolatable
{
    /**
     * @var string
     */
    protected $signature = 'transcodes:clear
        {--all : Clear all expired transcodes, including imported ones}';

    /**
     * @var string
     */
    protected $description = 'Force delete failed transcodes';

    public function handle(): void
    {
        $onlyFailed = ! $this->option('all');

        $transcodes = spin(
            message: 'Retrieving transcodes...',
            callback: fn () => Transcode::query()->when($onlyFailed, fn ($query) => $query->failed())->when(! $onlyFailed, fn ($query) => $query->expired())->lazy(),
        );

        if ($transcodes->isEmpty()) {
            info('No transcodes found to delete.');

            return;
        }

        table(
            headers: ['ID', 'State', 'Encoder', 'Created At'],
            rows: $transcodes->map(fn (Transcode $transcode) => [
                (string) $transcode->getKey(),
                (string) $transcode->state,
                (string) $transcode->encoder->value,
                (string) $transcode->created_at,
            ])->all(),
        );

        if (confirm('Are you sure you want to delete these transcodes?')) {
            $transcodes->each(function (Transcode $transcode) {
                info("deleting transcode ({$transcode->getKey()})");

                $transcode->delete();
            });
        }
    }
}
