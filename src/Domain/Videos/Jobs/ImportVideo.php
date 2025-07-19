<?php

declare(strict_types=1);

namespace Domain\Videos\Jobs;

use Domain\Users\Models\User;
use Domain\Videos\Actions\CreateNewVideoByImport;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;

class ImportVideo implements ShouldBeUnique, ShouldQueueAfterCommit
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var int
     */
    public $maxExceptions = 1;

    /**
     * @var int
     */
    public $timeout = 60 * 60 * 4;

    /**
     * @var int
     */
    public $uniqueFor = 60 * 60;

    /**
     * @var bool
     */
    public $failOnTimeout = true;

    /**
     * @var bool
     */
    public $deleteWhenMissingModels = true;

    public function __construct(
        public User $user,
        public string $disk,
        public string $path,
    ) {
        $this->onQueue('processing');
    }

    public function handle(): void
    {
        app(CreateNewVideoByImport::class)->handle($this->user, $this->disk, $this->path);
    }

    /**
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping("import:{$this->uniqueId()}"))->shared(),
        ];
    }

    public function uniqueId(): string
    {
        return hash('xxh128', implode(':', [$this->disk, $this->path]));
    }

    public function retryUntil(): \DateTime
    {
        return now()->addHour();
    }
}
