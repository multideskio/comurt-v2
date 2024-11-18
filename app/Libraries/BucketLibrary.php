<?php

namespace App\Libraries;

use Aws\Exception\AwsException;
use Aws\S3\S3Client;
//TODO: Add Log when have the time
class BucketLibrary
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
        } catch (AwsException $e) {
            if ($e->getAwsErrorCode() === 'NotFound' || $e->getAwsErrorCode() === 'NoSuchBucket') {
                try {
                    $this->s3->createBucket(['Bucket' => $bucket]);
                    $this->s3->waitUntil('BucketExists', ['Bucket' => $bucket]);
                } catch (AwsException $createException) {
                    throw $createException;
                }
            } else {
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
            return $result;
        } catch (AwsException $e) {
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
            return $result['Body'];
        } catch (AwsException $e) {
            return null;
        }
    }

    public function saveFile($bucket, $filePath, $key)
    {
        try {
            $fileContent = file_get_contents($filePath);
            return $this->uploadObject($bucket, $key, $fileContent);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getFile($bucket, $key)
    {
        try {
            $result = $this->s3->getObject([
                'Bucket' => $bucket,
                'Key'    => $key
            ]);
            return $result['Body'];
        } catch (AwsException $e) {
            return null;
        }
    }
}
