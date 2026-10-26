<?php

declare(strict_types=1);

namespace Modules\Api\Users\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Laravel\Passkeys\Passkey;

/**
 * @mixin Passkey
 */
class PasskeyResource extends JsonResource
{
    /**
     * @var bool
     */
    public $preserveKeys = true;

    /**
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'authenticator' => $this->authenticator,
            'last_used_at' => $this->last_used_at?->toDateTimeString(),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
