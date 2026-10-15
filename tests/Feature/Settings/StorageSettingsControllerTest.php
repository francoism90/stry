<?php

declare(strict_types=1);

use Domain\Shared\DataObjects\StorageCredentials;
use Domain\Shared\Enums\DiskContent;
use Domain\Users\Models\User;
use Domain\Videos\Models\Video;
use Foundation\Settings\StorageSettings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Modules\Web\Settings\Controllers\StorageSettingsController;
use Modules\Web\Settings\Rules\AllowedDiskRoot;
use Support\Filesystem\CheckDiskConnection;

beforeEach(function () {
    $this->root = storage_path('framework/testing/disks');

    foreach (['media', 'archive', 'import', 'exports', 'conversions'] as $directory) {
        File::ensureDirectoryExists("{$this->root}/{$directory}");
    }

    $this->app->bind(AllowedDiskRoot::class, fn () => new AllowedDiskRoot([$this->root]));

    $this->admin = User::factory()->create();
    $this->admin->assignRole('super-admin');

    $this->mock(CheckDiskConnection::class)
        ->shouldReceive('handle')
        ->andReturn(true)
        ->byDefault();
});

afterEach(fn () => File::deleteDirectory($this->root));

/**
 * @return array<string, mixed>
 */
function localDisk(string $name, array $contents, array $defaultFor = [], bool $serve = false): array
{
    return [
        'name' => $name,
        'label' => ucfirst($name),
        'driver' => 'local',
        'path' => storage_path("framework/testing/disks/{$name}"),
        'visibility' => 'private',
        'serve' => $serve,
        'contents' => $contents,
        'default_for' => $defaultFor,
    ];
}

/**
 * @return array<string, mixed>
 */
function s3Disk(string $name, ?string $key = 'AKIAEXAMPLEKEY12', ?string $secret = 'top-secret'): array
{
    return [
        'name' => $name,
        'label' => ucfirst($name),
        'driver' => 's3',
        'path' => '',
        'visibility' => 'private',
        'serve' => false,
        'contents' => ['conversions', 'thumbnails', 'reels'],
        'default_for' => ['conversions', 'thumbnails', 'reels'],
        'bucket' => 'conversions',
        'region' => 'us-east-1',
        'endpoint' => 'https://s3.example.com',
        'key' => $key,
        'secret' => $secret,
    ];
}

/**
 * Disks that hold every content, with "media" as the default for videos and captions.
 *
 * @return array<int, array<string, mixed>>
 */
function storageDisks(array $extra = [], ?array $conversions = null): array
{
    return [
        localDisk('media', ['videos', 'captions'], ['videos', 'captions']),
        localDisk('import', ['import'], ['import']),
        localDisk('exports', ['exports'], ['exports']),
        $conversions ?? localDisk('conversions', ['conversions', 'thumbnails', 'reels'], ['conversions', 'thumbnails', 'reels'], serve: true),
        ...$extra,
    ];
}

it('shows the disks to a super-admin without their secrets', function () {
    $response = $this->actingAs($this->admin)->get(action([StorageSettingsController::class, 'show']));

    $response->assertOk();
    $response->assertJsonPath('disks.3.name', 'conversions');
    $response->assertJsonMissingPath('disks.3.secret');
    $response->assertJsonMissingPath('disks.3.key');
    $response->assertJsonPath('roots.0.value', '/media');
    expect($response->json('disks.3'))->toHaveKey('has_secret');
});

it('denies a regular user from fetching or updating storage settings', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(action([StorageSettingsController::class, 'show']))->assertForbidden();
    $this->actingAs($user)->patch(action([StorageSettingsController::class, 'update']), ['disks' => storageDisks()])->assertForbidden();
});

it('saves the disks and writes new videos to the default disk', function () {
    $response = $this->actingAs($this->admin)->patch(action([StorageSettingsController::class, 'update']), [
        'disks' => [
            localDisk('media', ['videos', 'captions'], ['captions']),
            localDisk('archive', ['videos'], ['videos']),
            ...array_slice(storageDisks(), 1),
        ],
    ]);

    $response->assertValid();
    $response->assertRedirect();
    $response->assertInertiaFlash('type', 'success');

    expect(DiskContent::Videos->disk())->toBe('archive')
        ->and(DiskContent::Captions->disk())->toBe('media')
        ->and(config('filesystems.disks.archive.root'))->toBe("{$this->root}/archive");
});

it('rejects a local disk outside the allowed roots', function () {
    $disks = storageDisks();
    $disks[0]['path'] = base_path();

    $response = $this->actingAs($this->admin)->patch(action([StorageSettingsController::class, 'update']), ['disks' => $disks]);

    $response->assertInvalid(['disks.0.path' => 'The path must be inside']);
});

it('rejects a local disk that does not exist', function () {
    $disks = storageDisks();
    $disks[0]['path'] = "{$this->root}/missing";

    $response = $this->actingAs($this->admin)->patch(action([StorageSettingsController::class, 'update']), ['disks' => $disks]);

    $response->assertInvalid(['disks.0.path' => 'The path must be an existing, writable directory.']);
});

it('rejects a content without a default disk', function () {
    $disks = storageDisks();
    $disks[0]['default_for'] = ['captions'];

    $response = $this->actingAs($this->admin)->patch(action([StorageSettingsController::class, 'update']), ['disks' => $disks]);

    $response->assertInvalid(['disks']);
});

it('rejects two default disks for the same content', function () {
    $response = $this->actingAs($this->admin)->patch(action([StorageSettingsController::class, 'update']), [
        'disks' => storageDisks([localDisk('archive', ['videos'], ['videos'])]),
    ]);

    $response->assertInvalid(['disks']);
});

it('rejects a local disk that does not serve its files for thumbnails', function () {
    $response = $this->actingAs($this->admin)->patch(action([StorageSettingsController::class, 'update']), [
        'disks' => storageDisks(conversions: localDisk('conversions', ['conversions', 'thumbnails', 'reels'], ['conversions', 'thumbnails', 'reels'])),
    ]);

    $response->assertInvalid(['disks.3']);
});

it('rejects removing a disk that still holds media', function () {
    createClipFor(Video::factory()->create());

    $response = $this->actingAs($this->admin)->patch(action([StorageSettingsController::class, 'update']), [
        'disks' => [
            localDisk('archive', ['videos', 'captions'], ['videos', 'captions']),
            ...array_slice(storageDisks(), 1),
        ],
    ]);

    $response->assertInvalid(['disks']);
});

it('requires the secret that goes with an access key', function () {
    $response = $this->actingAs($this->admin)->patch(action([StorageSettingsController::class, 'update']), [
        'disks' => storageDisks(conversions: s3Disk('bucket', secret: null)),
    ]);

    $response->assertInvalid(['disks.3.secret']);
});

it('lets an S3 disk without settings of its own inherit the s3 disk', function () {
    config(['filesystems.disks.s3' => [
        'driver' => 's3',
        'key' => 'AKIADEFAULTKEY12',
        'secret' => 'default-secret',
        'region' => 'eu-west-1',
        'bucket' => 'default',
        'endpoint' => 'https://s3.example.com',
    ]]);

    $disk = [...s3Disk('conversions', key: null, secret: null), 'region' => null, 'endpoint' => null];

    $this->actingAs($this->admin)->patch(action([StorageSettingsController::class, 'update']), [
        'disks' => storageDisks(conversions: $disk),
    ])->assertValid();

    expect(config('filesystems.disks.conversions'))
        ->toMatchArray([
            'driver' => 's3',
            'key' => 'AKIADEFAULTKEY12',
            'secret' => 'default-secret',
            'region' => 'eu-west-1',
            'endpoint' => 'https://s3.example.com',
            'bucket' => 'conversions',
        ])
        ->and(app(StorageSettings::class)->refresh()->credentials)->toBe([]);
});

it('keeps the stored key and secret when they are left empty', function () {
    $settings = app(StorageSettings::class);
    $settings->credentials = ['conversions' => new StorageCredentials('AKIASTOREDKEY123', 'stored-secret')];
    $settings->save();

    $response = $this->actingAs($this->admin)->patch(action([StorageSettingsController::class, 'update']), [
        'disks' => storageDisks(conversions: s3Disk('conversions', key: null, secret: null)),
    ]);

    $response->assertValid();

    $credentials = app(StorageSettings::class)->refresh()->credentials['conversions'];

    expect($credentials->key)->toBe('AKIASTOREDKEY123')
        ->and($credentials->secret)->toBe('stored-secret');
});

it('stores the credentials encrypted', function () {
    $this->actingAs($this->admin)->patch(action([StorageSettingsController::class, 'update']), [
        'disks' => storageDisks(conversions: s3Disk('conversions')),
    ])->assertValid();

    $payload = DB::table('settings')->where('group', 'storage')->where('name', 'credentials')->value('payload');

    expect($payload)->not->toContain('top-secret')
        ->and(app(StorageSettings::class)->refresh()->credentials['conversions']->secret)->toBe('top-secret');
});

it('rejects an S3 disk that cannot be reached', function () {
    $this->mock(CheckDiskConnection::class)->shouldReceive('handle')->andReturn(false);

    $response = $this->actingAs($this->admin)->patch(action([StorageSettingsController::class, 'update']), [
        'disks' => storageDisks(conversions: s3Disk('conversions')),
    ]);

    $response->assertInvalid(['disks.3.bucket']);
});
