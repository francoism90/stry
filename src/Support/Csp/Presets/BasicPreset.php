<?php

declare(strict_types=1);

namespace Support\Csp\Presets;

use Foxws\Essentials\Csp\Presets\BasicPreset as Preset;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Uri;
use Spatie\Csp\Directive;
use Spatie\Csp\Policy;

/**
 * The essentials policy, plus the S3 disks. Their hosts aren't necessarily subdomains of the
 * app host (e.g. app on "stry.domain.tld" with storage on "stry-s3.domain.tld"), so their
 * configured origins are added explicitly rather than relying on "*.{$host}".
 */
class BasicPreset extends Preset
{
    public function configure(Policy $policy): void
    {
        parent::configure($policy);

        foreach ($this->getS3Origins() as $s3Origin) {
            $policy->add([Directive::CONNECT, Directive::FRAME, Directive::FONT, Directive::STYLE, Directive::IMG, Directive::MEDIA], $s3Origin);
        }
    }

    /**
     * The origins of every S3 disk, including those from the storage settings. The origin keeps
     * the scheme and port, so a disk like "http://rustfs:9000" isn't allowed as "https://rustfs".
     *
     * @return array<int, string>
     */
    protected function getS3Origins(): array
    {
        return collect((array) Config::get('filesystems.disks', []))
            ->filter(fn (mixed $disk): bool => is_array($disk) && ($disk['driver'] ?? null) === 's3')
            ->flatMap(fn (array $disk): array => [$disk['url'] ?? null, $disk['temporary_url'] ?? null])
            ->map(fn (mixed $url): ?string => $this->resolveOrigin((string) $url))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    protected function resolveOrigin(string $url): ?string
    {
        if ($url === '') {
            return null;
        }

        return Uri::of($url)->origin();
    }
}
