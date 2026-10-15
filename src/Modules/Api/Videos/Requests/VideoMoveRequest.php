<?php

declare(strict_types=1);

namespace Modules\Api\Videos\Requests;

use Domain\Shared\DataObjects\StorageDisk;
use Domain\Shared\Enums\DiskContent;
use Foundation\Settings\StorageSettings;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VideoMoveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $disks = collect(app(StorageSettings::class)->disksFor(DiskContent::Videos))
            ->map(fn (StorageDisk $disk): string => $disk->name)
            ->all();

        return [
            'disk' => ['required', 'string', Rule::in($disks)],
        ];
    }
}
