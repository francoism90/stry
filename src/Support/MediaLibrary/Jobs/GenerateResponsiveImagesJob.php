<?php

declare(strict_types=1);

namespace Support\MediaLibrary\Jobs;

use DateTime;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Spatie\MediaLibrary\ResponsiveImages\Jobs\GenerateResponsiveImagesJob as BaseGenerateResponsiveImagesJob;

class GenerateResponsiveImagesJob extends BaseGenerateResponsiveImagesJob
{
    /**
     * The maximum number of unhandled exceptions to allow before failing.
     *
     * @var int
     */
    public $maxExceptions = 1;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 60 * 30;

    /**
     * Indicate if the job should be marked as failed on timeout.
     *
     * @var bool
     */
    public $failOnTimeout = true;

    /**
     * Delete the job if its models no longer exist.
     *
     * @var bool
     */
    public $deleteWhenMissingModels = true;

    /**
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [
            new WithoutOverlapping($this->media->getKey()),
        ];
    }

    /**
     * Determine the time at which the job should timeout.
     */
    public function retryUntil(): DateTime
    {
        return now()->addDay();
    }
}
