<?php

declare(strict_types=1);

namespace Domain\Users\Commands;

use Domain\Users\Models\User;
use Illuminate\Console\Command;

class UserExistsCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'users:exists
        {--admin : Only count users with the admin role}
        {--super-admin : Only count users with the super-admin role}';

    /**
     * @var string
     */
    protected $description = 'Succeed when a user exists, for installers to skip creating one';

    public function handle(): int
    {
        $role = match (true) {
            $this->option('super-admin') => 'super-admin',
            $this->option('admin') => 'admin',
            default => null,
        };

        $query = User::query();

        if ($role !== null) {
            $query->role($role);
        }

        return $query->exists() ? self::SUCCESS : self::FAILURE;
    }
}
