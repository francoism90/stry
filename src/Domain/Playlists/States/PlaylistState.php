<?php

declare(strict_types=1);

namespace Domain\Playlists\States;

use Domain\Playlists\Models\Playlist;
use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

/**
 * @extends State<Playlist>
 */
abstract class PlaylistState extends State
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
