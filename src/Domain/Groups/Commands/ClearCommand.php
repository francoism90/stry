<?php

declare(strict_types=1);

namespace Domain\Groups\Commands;

use Domain\Groups\Enums\GroupType;
use Domain\Users\Models\User;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;

use function Laravel\Prompts\search;
use function Laravel\Prompts\spin;

class ClearCommand extends Command implements Isolatable
{
    /**
     * @var string
     */
    protected $signature = 'groups:clear {--type=viewed}';

    /**
     * @var string
     */
    protected $description = 'Detach all videos from groups of a given type.';

    public function handle(): void
    {
        $type = GroupType::from($this->option('type'));

        $user = search(
            label: 'Select user to find group for',
            validate: ['id' => 'required|exists:users,id'],
            placeholder: 'e.g. administrator@example.com',
            options: fn (string $value) => strlen($value) > 0
                ? User::whereLike('email', "%{$value}%")->pluck('email', 'id')->all()
                : User::limit(10)->pluck('email', 'id')->all(),
        );

        /** @var User $user */
        $user = User::findOrFail($user);

        $group = $user->findOrCreateGroup($type);

        spin(
            message: 'Detaching video...',
            callback: fn () => $group->videos()->detach()
        );
    }
}
