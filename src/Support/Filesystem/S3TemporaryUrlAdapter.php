<?php

declare(strict_types=1);

namespace Support\Filesystem;

use Aws\S3\S3Client;
use Illuminate\Filesystem\AwsS3V3Adapter;

class S3TemporaryUrlAdapter extends AwsS3V3Adapter
{
    private ?S3Client $presignClient = null;

    public function temporaryUrl($path, $expiration, array $options = []): string
    {
        if (! $this->hasPresignEndpointMismatch()) {
            return parent::temporaryUrl($path, $expiration, $options);
        }

        $client = $this->getPresignClient();

        $command = $client->getCommand('GetObject', array_merge([
            'Bucket' => $this->config['bucket'],
            'Key' => $this->prefixer->prefixPath($path),
        ], $options));

        return (string) $client->createPresignedRequest($command, $expiration, $options)->getUri();
    }

    public function temporaryUploadUrl($path, $expiration, array $options = []): array
    {
        if (! $this->hasPresignEndpointMismatch()) {
            return parent::temporaryUploadUrl($path, $expiration, $options);
        }

        $client = $this->getPresignClient();

        $command = $client->getCommand('PutObject', array_merge([
            'Bucket' => $this->config['bucket'],
            'Key' => $this->prefixer->prefixPath($path),
        ], $options));

        $signedRequest = $client->createPresignedRequest($command, $expiration);

        return [
            'url' => (string) $signedRequest->getUri(),
            'headers' => $signedRequest->getHeaders(),
        ];
    }

    private function hasPresignEndpointMismatch(): bool
    {
        $endpoint = $this->config['endpoint'] ?? null;
        $temporaryUrl = $this->config['temporary_url'] ?? null;

        return $temporaryUrl !== null && $endpoint !== $temporaryUrl;
    }

    private function getPresignClient(): S3Client
    {
        if ($this->presignClient === null) {
            $presignConfig = array_diff_key($this->config, array_flip([
                'driver', 'bucket', 'url', 'temporary_url', 'visibility',
                'throw', 'report', 'options', 'directory_separator',
                'stream_reads', 'read-only', 'prefix', 'root',
            ]));

            $presignConfig['endpoint'] = $this->config['temporary_url'];

            $this->presignClient = new S3Client($presignConfig);
        }

        return $this->presignClient;
    }
}
