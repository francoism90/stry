<?php

declare(strict_types=1);

namespace Support\System\Commands;

use Domain\Users\Models\User;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\warning;

class InstallApplicationCommand extends Command implements Isolatable
{
    /**
     * @var string
     */
    protected $signature = 'app:install';

    /**
     * @var string
     */
    protected $description = 'Set up the database, search indexes and an admin account of a new install';

    public function handle(): int
    {
        if ($this->call('migrate', ['--force' => true]) !== self::SUCCESS) {
            return self::FAILURE;
        }

        $this->call('db:seed', ['--force' => true]);
        $this->call('scout:sync', ['--import' => true]);

        if (confirm(label: 'Create the S3 buckets and apply their CORS policy?', default: false)) {
            $this->call('podman:s3-setup');
        }

        if (User::role('super-admin')->doesntExist()) {
            $this->createSuperAdmin();
        }

        info('stry is installed.');

        return self::SUCCESS;
    }

    protected function createSuperAdmin(): void
    {
        if (! $this->input->isInteractive()) {
            warning('There is no super-admin yet. Create one with "php artisan users:create --super-admin".');

            return;
        }

        if (confirm(label: 'Create a super-admin account?', default: true)) {
            $this->call('users:create', ['--super-admin' => true]);
        }
    }
}
