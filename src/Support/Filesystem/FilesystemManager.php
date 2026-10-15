<?php

declare(strict_types=1);

namespace Support\Filesystem;

use Aws\S3\S3Client;
use Illuminate\Filesystem\FilesystemManager as BaseFilesystemManager;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter as S3Adapter;
use League\Flysystem\AwsS3V3\PortableVisibilityConverter as AwsS3PortableVisibilityConverter;
use League\Flysystem\Visibility;

class FilesystemManager extends BaseFilesystemManager
{
    /**
     * A hash of the configuration each resolved disk was built from.
     *
     * @var array<string, string>
     */
    protected array $fingerprints = [];

    /**
     * Rebuild a resolved disk once its configuration changed, as disks from the storage settings can
     * change while a worker keeps running.
     *
     * @param  string  $name
     */
    protected function get($name)
    {
        $fingerprint = md5(serialize($this->getConfig($name)));

        if (($this->fingerprints[$name] ?? $fingerprint) !== $fingerprint) {
            unset($this->disks[$name]);
        }

        $this->fingerprints[$name] = $fingerprint;

        return parent::get($name);
    }

    /**
     * @param  array<string, mixed>  $config
     */
    public function createS3Driver(array $config): S3TemporaryUrlAdapter
    {
        $s3Config = $this->formatS3Config($config);

        $root = (string) ($s3Config['root'] ?? '');

        $visibility = new AwsS3PortableVisibilityConverter(
            $config['visibility'] ?? Visibility::PUBLIC
        );

        $streamReads = $s3Config['stream_reads'] ?? false;

        $client = new S3Client($s3Config);

        $adapter = new S3Adapter($client, $s3Config['bucket'], $root, $visibility, null, $config['options'] ?? [], $streamReads);

        return new S3TemporaryUrlAdapter(
            $this->createFlysystem($adapter, $config), $adapter, $s3Config, $client
        );
    }
}
