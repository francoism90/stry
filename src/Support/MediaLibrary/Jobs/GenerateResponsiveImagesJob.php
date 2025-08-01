<?php

declare(strict_types=1);

namespace Support\MediaLibrary\Jobs;

use Illuminate\Queue\Middleware\WithoutOverlapping;
use Spatie\MediaLibrary\ResponsiveImages\Jobs\GenerateResponsiveImagesJob as BaseGenerateResponsiveImagesJob;

class GenerateResponsiveImagesJob extends BaseGenerateResponsiveImagesJob
{
    /**
     * @var int
     */
    public $tries = 1;

    /**
     * @var int
     */
    public $backoff = 3;

    /**
     * @var int
     */
    public $timeout = 60 * 10;

    /**
     * @var int
     */
    public $maxExceptions = 1;

    /**
     * @var bool
     */
    public $failOnTimeout = true;

    /**
     * @var bool
     */
    public $deleteWhenMissingModels = true;

    /**
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping($this->media->getKey()))->releaseAfter(60),
        ];
    }
}
