<?php

declare(strict_types=1);

namespace Support\MediaLibrary\Jobs;

use DateTime;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Spatie\MediaLibrary\Conversions\Jobs\PerformConversionsJob as BasePerformConversionsJob;

class PerformConversionsJob extends BasePerformConversionsJob implements ShouldQueueAfterCommit
{
    /**
     * @var int
     */
    public $tries = 1;

    /**
     * @var int
     */
    public $maxExceptions = 1;

    /**
     * @var int
     */
    public $timeout = 60 * 20;

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
            (new WithoutOverlapping($this->media->getKey()))->releaseAfter(10),
        ];
    }

    public function retryUntil(): DateTime
    {
        return now()->addDay();
    }
}
