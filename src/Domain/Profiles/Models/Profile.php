<?php

declare(strict_types=1);

namespace Domain\Profiles\Models;

use Database\Factories\ProfileFactory;
use Domain\Profiles\States\ProfileState;
use Domain\Shared\Casts\AsDateTime;
use Domain\Users\Concerns\InteractsWithUser;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\ModelStates\HasStates;

class Profile extends Model
{
    use HasFactory;
    use HasStates;
    use HasUlids;
    use InteractsWithUser;
    use SoftDeletes;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'avatar',
        'is_kids',
        'is_primary',
        'state',
        'settings',
    ];

    /**
     * @var array<int, string>
     */
    protected $hidden = [
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'is_kids' => 'boolean',
            'is_primary' => 'boolean',
            'state' => ProfileState::class,
            'settings' => AsArrayObject::class,
            'created_at' => AsDateTime::class,
            'updated_at' => AsDateTime::class,
            'deleted_at' => AsDateTime::class,
        ];
    }

    protected static function newFactory(): ProfileFactory
    {
        return ProfileFactory::new();
    }

    public function uniqueIds(): array
    {
        return ['ulid'];
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    public static function findFromUlid(Profile|string $value): ?Profile
    {
        if ($value instanceof Profile) {
            return $value;
        }

        return Profile::query()->firstWhere('ulid', $value);
    }
}
