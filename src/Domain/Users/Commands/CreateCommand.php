<?php

declare(strict_types=1);

namespace Domain\Users\Commands;

use Domain\Users\Actions\CreateNewUser;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

class CreateCommand extends Command implements Isolatable
{
    /**
     * @var string
     */
    protected $signature = 'users:create';

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

        app(CreateNewUser::class)->create(compact('name', 'email', 'password'));

        $this->components->info('User has been created successfully.');
    }
}
