<?php

declare(strict_types=1);

namespace Domain\Groups\States;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class GroupState extends State
{
    abstract public function label(): string;

    abstract public function color(): string;

    abstract public function icon(): string;

    public function toArray(): array
    {
        return [
            'name' => $this->getValue(),
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
