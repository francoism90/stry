<?php

declare(strict_types=1);

namespace Domain\Media\Models;

use Domain\Media\States;
use Domain\Media\States\TranscodeState;
use Domain\Shared\Casts\AsDateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Config;
use Spatie\ModelStates\HasStates;

class Transcode extends Model
{
    use HasFactory;
    use HasStates;

    protected $fillable = [
        'media_id',
        'preset',
        'state',
        'error_message',
        'retry_count',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'retry_count' => 'integer',
            'started_at' => AsDateTime::class,
            'completed_at' => AsDateTime::class,
            'state' => TranscodeState::class,
        ];
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function isPending(): bool
    {
        return $this->state->equals(States\Pending::class);
    }

    public function isProcessing(): bool
    {
        return $this->state->equals(States\Processing::class);
    }

    public function isCompleted(): bool
    {
        return $this->state->equals(States\Completed::class);
    }

    public function isFailed(): bool
    {
        return $this->state->equals(States\Failed::class);
    }

    public function getOptions(): array
    {
        return Config::array("transcodes.presets.{$this->preset}", []);
    }

    public static function getDisk(): string
    {
        return Config::string('transcodes.disk', 'transcodes');
    }
}
