<?php

declare(strict_types=1);

namespace Modules\Web\Settings\Rules;

use Closure;
use Domain\Shared\Enums\DiskContent;
use Domain\Shared\Enums\DiskDriver;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Files that are shown in the browser, such as thumbnails and reels, are loaded through temporary
 * URLs, which only S3 disks and local disks that serve their files can make.
 */
class DiskSupportsContents implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_array($value)) {
            return;
        }

        $isLocal = ($value['driver'] ?? null) === DiskDriver::Local->value;

        if (! $isLocal || filter_var($value['serve'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            return;
        }

        $contents = collect((array) ($value['contents'] ?? []))
            ->map(fn (mixed $content): ?DiskContent => DiskContent::tryFrom((string) $content))
            ->filter(fn (?DiskContent $content): bool => $content?->requiresTemporaryUrls() ?? false);

        if ($contents->isEmpty()) {
            return;
        }

        $fail(__('A local disk must serve its files to hold :contents.', [
            'contents' => $contents->map(fn (DiskContent $content): string => $content->label())->implode(', '),
        ]));
    }
}
