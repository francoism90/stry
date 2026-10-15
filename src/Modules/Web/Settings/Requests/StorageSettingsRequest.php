<?php

declare(strict_types=1);

namespace Modules\Web\Settings\Requests;

use Domain\Media\Models\Media;
use Domain\Shared\DataObjects\StorageCredentials;
use Domain\Shared\DataObjects\StorageDisk;
use Domain\Shared\Enums\DiskContent;
use Domain\Shared\Enums\DiskDriver;
use Foundation\Settings\StorageSettings;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Modules\Web\Settings\Rules\AllowedDiskRoot;
use Modules\Web\Settings\Rules\DiskSupportsContents;
use Support\Filesystem\CheckDiskConnection;

class StorageSettingsRequest extends FormRequest
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
        return [
            'disks' => ['required', 'array', 'min:1'],
            'disks.*' => ['required', 'array', new DiskSupportsContents],
            'disks.*.name' => ['required', 'string', 'max:64', 'regex:/^[a-z0-9][a-z0-9_-]*$/', 'distinct', Rule::notIn($this->getReservedDiskNames())],
            'disks.*.label' => ['required', 'string', 'max:100'],
            'disks.*.driver' => ['required', Rule::enum(DiskDriver::class)],
            'disks.*.path' => ['nullable', 'string', 'max:255'],
            'disks.*.visibility' => ['required', Rule::in(['public', 'private'])],
            'disks.*.serve' => ['boolean'],
            'disks.*.contents' => ['array'],
            'disks.*.contents.*' => [Rule::enum(DiskContent::class)],
            'disks.*.default_for' => ['array'],
            'disks.*.default_for.*' => [Rule::enum(DiskContent::class)],
            'disks.*.bucket' => ['nullable', 'string', 'max:255'],
            'disks.*.region' => ['nullable', 'string', 'max:64'],
            'disks.*.endpoint' => ['nullable', 'url', 'max:255'],
            'disks.*.url' => ['nullable', 'url', 'max:255'],
            'disks.*.temporary_url' => ['nullable', 'url', 'max:255'],
            'disks.*.use_path_style_endpoint' => ['nullable', 'boolean'],
            'disks.*.cache_control' => ['nullable', 'string', 'max:255'],
            'disks.*.key' => ['nullable', 'string', 'max:255'],
            'disks.*.secret' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            $this->validateLocalPaths(...),
            $this->validateDefaults(...),
            $this->validateSecrets(...),
            $this->validateRemovedDisks(...),
            $this->validateConnections(...),
        ];
    }

    /**
     * @return Collection<int, StorageDisk>
     */
    public function disks(): Collection
    {
        return collect($this->validated('disks'))
            ->map(fn (array $disk): StorageDisk => StorageDisk::from([
                ...$disk,
                'path' => (string) ($disk['path'] ?? ''),
                'serve' => (bool) ($disk['serve'] ?? false),
                'contents' => array_values(array_unique($disk['contents'] ?? [])),
                'default_for' => array_values(array_unique($disk['default_for'] ?? [])),
            ]));
    }

    /**
     * The credentials S3 disks set themselves, keeping the stored key and secret when they're left
     * empty. Disks without any use the credentials of the "s3" disk.
     *
     * @return array<string, StorageCredentials>
     */
    public function credentials(): array
    {
        $stored = $this->getSettings()->credentials;

        return collect($this->validated('disks'))
            ->filter(fn (array $disk): bool => $disk['driver'] === DiskDriver::S3->value)
            ->mapWithKeys(fn (array $disk): array => [$disk['name'] => new StorageCredentials(
                key: filled($disk['key'] ?? null) ? (string) $disk['key'] : ($stored[$disk['name']]->key ?? ''),
                secret: filled($disk['secret'] ?? null) ? (string) $disk['secret'] : ($stored[$disk['name']]->secret ?? ''),
            )])
            ->filter(fn (StorageCredentials $credentials): bool => $credentials->key !== '' || $credentials->hasSecret())
            ->all();
    }

    protected function validateLocalPaths(Validator $validator): void
    {
        foreach ($this->getSubmittedDisks() as $index => $disk) {
            if (($disk['driver'] ?? null) !== DiskDriver::Local->value) {
                continue;
            }

            $rule = app(AllowedDiskRoot::class);

            $rule->validate('path', $disk['path'] ?? '', fn (string $message) => $validator->errors()->add("disks.{$index}.path", $message));
        }
    }

    /**
     * Each kind of content is written to one default disk, which must hold it. Content without a
     * default falls back to its built-in disk, which only works when that disk isn't managed here.
     */
    protected function validateDefaults(Validator $validator): void
    {
        $disks = $this->getSubmittedDisks();

        foreach ($disks as $index => $disk) {
            $missing = array_diff((array) ($disk['default_for'] ?? []), (array) ($disk['contents'] ?? []));

            if ($missing !== []) {
                $validator->errors()->add("disks.{$index}.default_for", __('A disk can only be the default for what it holds.'));
            }
        }

        $reserved = $this->getReservedDiskNames();

        foreach (DiskContent::cases() as $content) {
            $defaults = $disks->filter(fn (array $disk): bool => in_array($content->value, (array) ($disk['default_for'] ?? []), true));

            if ($defaults->count() > 1) {
                $validator->errors()->add('disks', __('Only one disk can be the default for :content.', ['content' => $content->label()]));
            }

            if ($defaults->isEmpty() && ! in_array($content->defaultDisk(), $reserved, true)) {
                $validator->errors()->add('disks', __('Choose a default disk for :content.', ['content' => $content->label()]));
            }
        }
    }

    /**
     * An S3 disk either uses the credentials of the "s3" disk, or sets both a key and a secret. They
     * are never shown again, so a stored key or secret is kept when left empty.
     */
    protected function validateSecrets(Validator $validator): void
    {
        $stored = $this->getSettings()->credentials;

        foreach ($this->getSubmittedDisks() as $index => $disk) {
            if (($disk['driver'] ?? null) !== DiskDriver::S3->value) {
                continue;
            }

            $credentials = $stored[(string) ($disk['name'] ?? '')] ?? null;

            $hasKey = filled($disk['key'] ?? null) || filled($credentials?->key);
            $hasSecret = filled($disk['secret'] ?? null) || ($credentials?->hasSecret() ?? false);

            if ($hasKey && ! $hasSecret) {
                $validator->errors()->add("disks.{$index}.secret", __('Enter the secret that goes with this access key.'));
            }

            if ($hasSecret && ! $hasKey) {
                $validator->errors()->add("disks.{$index}.key", __('Enter the access key that goes with this secret.'));
            }
        }
    }

    /**
     * Disks that still hold media can't be removed or renamed, as the media refer to them by name.
     */
    protected function validateRemovedDisks(Validator $validator): void
    {
        $submitted = $this->getSubmittedDisks()->pluck('name')->all();

        $removed = collect($this->getSettings()->disks)
            ->map(fn (StorageDisk $disk): string => $disk->name)
            ->diff($submitted)
            ->values();

        if ($removed->isEmpty()) {
            return;
        }

        $inUse = Media::query()
            ->whereIn('disk', $removed)
            ->pluck('disk')
            ->merge(Media::query()->whereIn('conversions_disk', $removed)->pluck('conversions_disk'))
            ->unique();

        foreach ($inUse as $name) {
            $validator->errors()->add('disks', __('The :disk disk still holds media and can\'t be removed.', ['disk' => $name]));
        }
    }

    protected function validateConnections(Validator $validator): void
    {
        if ($validator->errors()->isNotEmpty()) {
            return;
        }

        $credentials = $this->credentials();

        $this->disks()
            ->reject(fn (StorageDisk $disk): bool => $disk->isLocal())
            ->each(function (StorageDisk $disk, int $index) use ($validator, $credentials): void {
                if (! app(CheckDiskConnection::class)->handle($disk->toConfig($credentials[$disk->name] ?? null))) {
                    $validator->errors()->add("disks.{$index}.bucket", __('The bucket can\'t be reached with these settings.'));
                }
            });
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function getSubmittedDisks(): Collection
    {
        return collect((array) $this->input('disks', []))->filter(fn (mixed $disk): bool => is_array($disk));
    }

    /**
     * The disks defined in config/filesystems.php, which can't be replaced here.
     *
     * @return array<int, string>
     */
    protected function getReservedDiskNames(): array
    {
        return collect(Config::array('filesystems.disks', []))
            ->filter()
            ->keys()
            ->diff(Config::array('filesystems.managed', []))
            ->values()
            ->all();
    }

    protected function getSettings(): StorageSettings
    {
        return app(StorageSettings::class);
    }
}
