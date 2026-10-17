<?php

declare(strict_types=1);

namespace Modules\Web\Settings\Controllers;

use Domain\Shared\Actions\UpdateStorageSettings;
use Domain\Shared\DataObjects\StorageDisk;
use Domain\Shared\Enums\DiskContent;
use Domain\Shared\Enums\DiskDriver;
use Foundation\Settings\StorageSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Modules\Web\Settings\Requests\StorageSettingsRequest;
use Spatie\LaravelOptions\Options;

class StorageSettingsController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    /**
     * The disks, without their secrets: S3 disks only show a masked access key. The "s3" defaults
     * are what S3 disks fall back to, without its credentials.
     */
    public function show(StorageSettings $settings): JsonResponse
    {
        Gate::authorize('manage-application-settings');

        $s3 = StorageDisk::getDefaultS3Config();

        return response()->json([
            'disks' => collect($settings->disks)->map(fn (StorageDisk $disk): array => [
                ...$disk->toArray(),
                'masked_key' => ($settings->credentials[$disk->name] ?? null)?->maskedKey(),
                'has_secret' => ($settings->credentials[$disk->name] ?? null)?->hasSecret() ?? false,
            ])->all(),
            'drivers' => Options::forEnum(DiskDriver::class),
            'contents' => collect(DiskContent::cases())->map(fn (DiskContent $content): array => [
                'value' => $content->value,
                'label' => $content->label(),
                'requires_temporary_urls' => $content->requiresTemporaryUrls(),
                'prefers_local' => $content->prefersLocal(),
            ])->all(),
            's3_defaults' => [
                'bucket' => $s3['bucket'] ?? null,
                'region' => $s3['region'] ?? null,
                'endpoint' => $s3['endpoint'] ?? null,
                'url' => $s3['url'] ?? null,
                'temporary_url' => $s3['temporary_url'] ?? null,
                'use_path_style_endpoint' => (bool) ($s3['use_path_style_endpoint'] ?? false),
                'has_credentials' => filled($s3['key'] ?? null),
            ],
        ]);
    }

    public function update(StorageSettingsRequest $request): Response|RedirectResponse
    {
        Gate::authorize('manage-application-settings');

        app(UpdateStorageSettings::class)->handle(
            disks: $request->disks()->all(),
            credentials: $request->credentials(),
        );

        if ($request->wantsJson()) {
            return response()->noContent();
        }

        toast(title: __('Settings saved'), description: __('Restart the app and its queue workers to use the new storage settings.'));

        return back();
    }
}
