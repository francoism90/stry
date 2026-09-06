<?php

declare(strict_types=1);

namespace App\Web\Chapters\Responses;

use Domain\Chapters\Enums\ChapterType;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;
use Spatie\LaravelOptions\Options;

readonly class ChapterTypeOptionsProperty implements ProvidesInertiaProperty
{
    public static function options(): Options
    {
        return Options::forEnum(ChapterType::class);
    }

    public function toInertiaProperty(PropertyContext $context): mixed
    {
        return once(fn (): Options => self::options());
    }
}
