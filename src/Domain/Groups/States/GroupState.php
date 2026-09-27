<?php

declare(strict_types=1);

namespace Domain\Groups\States;

use Domain\Groups\Models\Group;
use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

/**
 * @extends State<Group>
 */
abstract class GroupState extends State
{
    abstract public function label(): string;

    abstract public function color(): string;

    abstract public function icon(): string;

    /**
     * @return array{name: string, label: string, icon: string, color: string}
     */
    public function toArray(): array
    {
        return [
            'name' => $this->getValue(),
            'label' => $this->label(),
            'icon' => $this->icon(),
            'color' => $this->color(),
        ];
    }

    public static function config(): StateConfig
    {
        return parent::config()
            ->default(Verified::class)
            ->allowAllTransitions();
    }
}
