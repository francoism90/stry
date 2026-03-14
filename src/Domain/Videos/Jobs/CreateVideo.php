<?php

declare(strict_types=1);

namespace Domain\Videos\Jobs;

use Domain\Users\Models\User;
use Domain\Videos\Actions\CreateVideoByImport;
use Domain\Videos\DataObjects\VideoFile;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;

class CreateVideo implements ShouldBeUnique, ShouldQueueAfterCommit
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var int
     */
    public $tries = 1;

    /**
     * @var int
     */
    public $timeout = 3600;

    /**
     * @var int
     */
    public $uniqueFor = 300;

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

    public function __construct(
        public User $user,
        public VideoFile $file,
    ) {
        $this->onQueue('processing');
    }

    public function handle(): void
    {
        app(CreateVideoByImport::class)->handle($this->user, $this->file);
    }

    /**
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping($this->uniqueId()))->dontRelease(),
        ];
    }

    public function uniqueId(): string
    {
        return (string) "{$this->file->disk}:{$this->file->path}";
    }
}
