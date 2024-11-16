<?php

namespace App\Helpers;

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

class BucketSave
{
    protected $s3;

    public function __construct()
    {
        date_default_timezone_set('America/Los_Angeles');
        $this->s3 = new S3Client([
            'version' => getenv('S3_VERSION'),
            'region'  => getenv('S3_REGION'),
            'endpoint' => getenv('S3_ENDPOINT'),
            'use_path_style_endpoint' => filter_var(getenv('S3_USE_PATH_STYLE_ENDPOINT'), FILTER_VALIDATE_BOOLEAN),
            'credentials' => [
                'key'    => getenv('S3_KEY'),
                'secret' => getenv('S3_SECRET'),
            ],
        ]);
    }

    public function ensureBucketExists($bucket)
    {
        try {
            $this->s3->headBucket(['Bucket' => $bucket]);
            echo "Bucket '{$bucket}' already exists.\n";
        } catch (AwsException $e) {
            if ($e->getAwsErrorCode() === 'NotFound' || $e->getAwsErrorCode() === 'NoSuchBucket') {
                try {
                    echo "Bucket '{$bucket}' does not exist. Creating...\n";
                    $this->s3->createBucket(['Bucket' => $bucket]);
                    $this->s3->waitUntil('BucketExists', ['Bucket' => $bucket]);
                    echo "Bucket '{$bucket}' created successfully.\n";
                } catch (AwsException $createException) {
                    echo "Error creating bucket: " . $createException->getMessage() . "\n";
                    throw $createException;
                }
            } else {
                echo "Error checking bucket: " . $e->getMessage() . "\n";
                throw $e;
            }
        }
    }



    public function uploadObject($bucket, $key, $body)
    {
        try {
            $this->ensureBucketExists($bucket);

            $result = $this->s3->putObject([
                'Bucket' => $bucket,
                'Key'    => $key,
                'Body'   => $body
            ]);
            echo "Object uploaded successfully to '{$bucket}/{$key}'.\n";
            return $result;
        } catch (AwsException $e) {
            echo "Error: " . $e->getMessage() . "\n";
            return null;
        }
    }

    public function downloadObject($bucket, $key, $saveAs)
    {
        try {
            $result = $this->s3->getObject([
                'Bucket' => $bucket,
                'Key'    => $key,
                'SaveAs' => $saveAs
            ]);
            echo "Object downloaded successfully: " . $result['Body'] . "\n";
            return $result['Body'];
        } catch (AwsException $e) {
            echo "Error: " . $e->getMessage() . "\n";
            return null;
        }
    }
}
