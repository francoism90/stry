<?php

declare(strict_types=1);

namespace Domain\Users\Commands;

use Domain\Users\Actions\CreateNewUser;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;

use function Laravel\Prompts\info;
use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

class CreateUserCommand extends Command implements Isolatable
{
    /**
     * @var string
     */
    protected $signature = 'users:create {--admin : Assign the admin role to the user} {--super-admin : Assign the super-admin role to the user}';

    /**
     * @var string
     */
    protected $description = 'Create a new user';

    public function handle(): void
    {
        $name = text(
            label: 'Name',
            required: true,
        );

        $email = text(
            label: 'Email',
            required: true,
        );

        $password = password(
            label: 'Password',
            required: true,
        );

        $user = app(CreateNewUser::class)->create(compact('name', 'email', 'password'));

        if ($this->option('super-admin')) {
            $user->assignRole('super-admin');
        } elseif ($this->option('admin')) {
            $user->assignRole('admin');
        }

        info("User has been created successfully ({$user->email}).");
    }
}
