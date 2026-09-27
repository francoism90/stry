<?php

declare(strict_types=1);

namespace Domain\Transcodes\States;

use Domain\Transcodes\Models\Transcode;
use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

/**
 * @extends State<Transcode>
 */
abstract class TranscodeState extends State
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
            ->default(Pending::class)
            ->allowAllTransitions();
    }
}
