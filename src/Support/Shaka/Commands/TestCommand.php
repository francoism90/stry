<?php

declare(strict_types=1);

namespace Support\Shaka\Commands;

use Foxws\Shaka\Facades\Shaka;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Support\Facades\Config;

use function Laravel\Prompts\info;

class TestCommand extends Command implements Isolatable
{
    /**
     * @var string
     */
    protected $signature = 'shaka:test';

    /**
     * @var string
     */
    protected $description = 'Test command for Shaka package';

    public function handle(): void
    {
        $result = Shaka::fromDisk('s3')
            ->open('example.mp4')
            ->addVideoStream('example.mp4', 'video.mp4')
            ->addAudioStream('example.mp4', 'audio.mp4')
            ->withMpdOutput('manifest.mpd')
            ->export();

        dd($result);
    }
}
