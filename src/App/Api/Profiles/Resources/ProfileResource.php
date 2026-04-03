<?php

declare(strict_types=1);

namespace App\Api\Profiles\Resources;

use Domain\Profiles\Models\Profile;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Profile
 */
class ProfileResource extends JsonResource
{
    /**
     * @var bool
     */
    public $preserveKeys = true;

    public function toArray($request): array
    {
        $currentProfile = $request->attributes->get('profiles.current');

        return [
            'id' => (string) $this->getRouteKey(),
            'name' => $this->name,
            'avatar' => $this->avatar,
            'is_kids' => $this->isKids(),
            'is_primary' => $this->isPrimary(),
            'state' => $this->state->toArray(),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
