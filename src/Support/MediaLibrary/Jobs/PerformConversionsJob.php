<?php

declare(strict_types=1);

namespace Support\MediaLibrary\Jobs;

use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Spatie\MediaLibrary\Conversions\Jobs\PerformConversionsJob as BasePerformConversionsJob;

class PerformConversionsJob extends BasePerformConversionsJob implements ShouldBeUniqueUntilProcessing, ShouldQueueAfterCommit
{
    /**
     * @var int
     */
    public $uniqueFor = 120;

    /**
     * @var int
     */
    public $tries = 3;

    /**
     * @var int
     */
    public $maxExceptions = 1;

    /**
     * @var int
     */
    public $timeout = 300;

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
            (new WithoutOverlapping($this->uniqueId()))
                ->expireAfter($this->timeout)
                ->releaseAfter(30),
        ];
    }

    public function uniqueId(): string
    {
        return (string) $this->media->getKey();
    }
}
