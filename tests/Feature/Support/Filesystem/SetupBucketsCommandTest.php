<?php

use Aws\CommandInterface;
use Aws\MockHandler;
use Aws\Result;
use Aws\S3\Exception\S3Exception;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\artisan;

/**
 * Points the s3 disk at a mocked S3 API and returns the operations it receives.
 *
 * @param  array<string, string>  $errors  Bucket => AWS error code for its "CreateBucket" call.
 */
function fakeS3Api(array $errors = []): ArrayObject
{
    $operations = new ArrayObject;

    $disk = Storage::build([
        'driver' => 's3',
        'key' => 'key',
        'secret' => 'secret',
        'region' => 'us-east-1',
        'bucket' => 'local',
        'endpoint' => 'http://rustfs:9000',
        'use_path_style_endpoint' => true,
    ]);

    $handler = new MockHandler;
    $handler->append(...array_fill(0, 10, function (CommandInterface $command) use ($operations, $errors) {
        $operations[] = "{$command->getName()} {$command['Bucket']}";

        if ($command->getName() === 'CreateBucket' && isset($errors[$command['Bucket']])) {
            return new S3Exception('Failed', $command, ['code' => $errors[$command['Bucket']]]);
        }

        return new Result;
    }));

    $disk->getClient()->getHandlerList()->setHandler($handler);

    Storage::set('s3', $disk);

    return $operations;
}

it('creates the buckets and applies the cors policy', function () {
    $operations = fakeS3Api();

    artisan('app:s3-setup')->assertSuccessful();

    expect($operations->getArrayCopy())->toBe([
        'CreateBucket local',
        'CreateBucket conversions',
        'PutBucketCors conversions',
    ]);
});

it('accepts buckets that already exist', function () {
    fakeS3Api(['local' => 'BucketAlreadyOwnedByYou']);

    artisan('app:s3-setup')->assertSuccessful();
});

it('fails when a bucket cannot be created', function () {
    $operations = fakeS3Api(['conversions' => 'AccessDenied']);

    artisan('app:s3-setup')
        ->expectsOutputToContain('Failed to create: conversions')
        ->assertFailed();

    expect($operations->getArrayCopy())->not->toContain('PutBucketCors conversions');
});
