<?php

declare(strict_types=1);

namespace Domain\Groups\Commands;

use Domain\Groups\Enums\GroupType;
use Domain\Users\Models\User;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\search;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\table;
use function Laravel\Prompts\warning;

class ClearGroupCommand extends Command implements Isolatable
{
    /**
     * @var string
     */
    protected $signature = 'groups:clear
        {--group=viewed : The group type to clear (liked, saved, viewed)}';

    /**
     * @var string
     */
    protected $description = "Detach all videos from a user's group";

    public function handle(): void
    {
        $type = GroupType::tryFrom((string) $this->option('group'));

        if (! $type instanceof GroupType || in_array($type, [GroupType::Custom, GroupType::Mixer])) {
            warning('The --group option must be one of: liked, saved, viewed.');

            return;
        }

        $userId = search(
            label: 'Select user to clear group for',
            validate: ['id' => 'required|exists:users,id'],
            placeholder: 'e.g. administrator@example.com',
            options: fn (string $value) => strlen($value) > 0
                ? User::query()->whereLike('email', "%{$value}%")->pluck('email', 'id')->all()
                : User::query()->limit(10)->pluck('email', 'id')->all(),
        );

        $user = User::findOrFail($userId);

        $group = spin(
            message: 'Retrieving group...',
            callback: fn () => $user->findOrCreateGroup($type),
        );

        $count = $group->videos()->count();

        if ($count === 0) {
            info("The {$type->label()} group is already empty.");

            return;
        }

        table(
            headers: ['ID', 'Type', 'Videos'],
            rows: [[
                (string) $group->getKey(),
                (string) $group->type->label(),
                (string) $count,
            ]],
        );

        if (confirm("Are you sure you want to detach all {$count} video(s) from the {$type->label()} group?")) {
            $group->videos()->detach();

            info('Done.');
        }
    }
}
