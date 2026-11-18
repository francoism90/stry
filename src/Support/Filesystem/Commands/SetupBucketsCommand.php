<?php

declare(strict_types=1);

namespace Support\Filesystem\Commands;

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Illuminate\Console\Command;
use Illuminate\Filesystem\AwsS3V3Adapter;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;

class SetupBucketsCommand extends Command
{
    /**
     * Browsers check CORS even for signed URLs, so the buckets they load
     * files from need this. The buckets themselves stay private.
     */
    protected const array CorsPolicy = [
        'CORSRules' => [
            [
                'AllowedOrigins' => ['*'],
                'AllowedMethods' => ['GET', 'HEAD'],
                'AllowedHeaders' => ['*'],
                'ExposeHeaders' => ['Content-Length', 'Content-Range', 'Accept-Ranges', 'ETag'],
                'MaxAgeSeconds' => 7200,
            ],
        ],
    ];

    /**
     * @var string
     */
    protected $signature = 'app:s3-setup';

    /**
     * @var string
     */
    protected $description = 'Create the S3 buckets on the s3 disk and apply their CORS policy';

    public function handle(): int
    {
        $disk = Storage::disk('s3');

        if (! $disk instanceof AwsS3V3Adapter) {
            error('The s3 disk is not an S3 disk.');

            return self::FAILURE;
        }

        $client = $disk->getClient();

        /** @var list<string> $buckets */
        $buckets = Config::array('filesystems.buckets');

        $failed = array_values(array_filter($buckets, fn (string $bucket): bool => ! $this->createBucket($client, $bucket)));

        if ($failed !== []) {
            error('Failed to create: '.implode(', ', $failed));

            return self::FAILURE;
        }

        info('Created: '.implode(', ', $buckets));

        /** @var list<string> $corsBuckets */
        $corsBuckets = Config::array('filesystems.cors_buckets');

        $failed = array_values(array_filter($corsBuckets, fn (string $bucket): bool => ! $this->applyCorsPolicy($client, $bucket)));

        if ($failed !== []) {
            error('Failed to apply the CORS policy to: '.implode(', ', $failed));

            return self::FAILURE;
        }

        if ($corsBuckets !== []) {
            info('Applied the CORS policy to: '.implode(', ', $corsBuckets));
        }

        return self::SUCCESS;
    }

    protected function createBucket(S3Client $client, string $bucket): bool
    {
        try {
            $client->createBucket(['Bucket' => $bucket]);

            return true;
        } catch (S3Exception $exception) {
            return in_array($exception->getAwsErrorCode(), ['BucketAlreadyOwnedByYou', 'BucketAlreadyExists'], true);
        }
    }

    protected function applyCorsPolicy(S3Client $client, string $bucket): bool
    {
        try {
            $client->putBucketCors(['Bucket' => $bucket, 'CORSConfiguration' => self::CorsPolicy]);

            return true;
        } catch (S3Exception) {
            return false;
        }
    }
}
