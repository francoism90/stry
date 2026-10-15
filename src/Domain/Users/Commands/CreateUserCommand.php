<?php

declare(strict_types=1);

namespace Domain\Users\Commands;

use Domain\Users\Actions\CreateNewUser;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Console\Input\StreamableInputInterface;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

class CreateUserCommand extends Command implements Isolatable
{
    /**
     * @var string
     */
    protected $signature = 'users:create
        {--name= : The name of the user}
        {--email= : The email address of the user}
        {--password-stdin : Read the password from stdin, so it stays out of the arguments}
        {--admin : Assign the admin role to the user}
        {--super-admin : Assign the super-admin role to the user}';

    /**
     * @var string
     */
    protected $description = 'Create a new user';

    public function handle(): int
    {
        $name = $this->option('name') ?: $this->promptFor('Name', 'text');
        $email = $this->option('email') ?: $this->promptFor('Email', 'text');
        $password = $this->option('password-stdin') ? $this->readPasswordFromStdin() : $this->promptFor('Password', 'password');

        if ($name === null || $email === null || $password === null) {
            error('Pass --name, --email and --password-stdin when running without prompts.');

            return self::FAILURE;
        }

        try {
            $user = app(CreateNewUser::class)->create(compact('name', 'email', 'password'));
        } catch (ValidationException $exception) {
            foreach ($exception->validator->errors()->all() as $message) {
                error($message);
            }

            return self::FAILURE;
        }

        $role = match (true) {
            $this->option('super-admin') => 'super-admin',
            $this->option('admin') => 'admin',
            default => null,
        };

        if ($role !== null) {
            $user->assignRole($role);
        }

        info("User has been created successfully ({$user->email}).");

        return self::SUCCESS;
    }

    /**
     * Prompts for a value, or gives null when the command runs without prompts.
     */
    protected function promptFor(string $label, string $type): ?string
    {
        if (! $this->input->isInteractive()) {
            return null;
        }

        return $type === 'password'
            ? password(label: $label, required: true)
            : text(label: $label, required: true);
    }

    protected function readPasswordFromStdin(): ?string
    {
        $stream = $this->input instanceof StreamableInputInterface ? $this->input->getStream() : null;

        $password = rtrim((string) stream_get_contents($stream ?? STDIN), "\r\n");

        return $password !== '' ? $password : null;
    }
}
