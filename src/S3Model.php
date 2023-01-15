<?php

namespace Hasdemir\Base;

use Aws\CloudFront\CloudFrontClient;
use Aws\CloudFront\UrlSigner;
use Aws\S3\S3Client;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Net\SSH2;
use DateTime;

class S3Model
{
  protected string $provider = 'Aws';
  protected object $client;
  //protected object $cloudfront;
  //protected string $cloudfront_url;
  protected string $bucket;
  const PUT_OBJECT = 'PutObject';
  const GET_OBJECT = 'GetObject';
  const EXPIRES_TIME = '+7 days';
  const CF_EXPIRES_TIME = '+10 minutes';

  public function __construct()
  {
    Codes::currentJob($this->provider . '-s3');
    $this->bucket = $_ENV['AWS_BUCKET_FREE'];
    $args = [
      'version' => 'latest',
      'region' => $_ENV['AWS_REGION'] ?? '',
      'credentials' => [
        'key' => $_ENV['AWS_ACCESS_KEY_ID'] ?? '',
        'secret' => $_ENV['AWS_SECRET_KEY'] ?? ''
      ]
    ];
    $this->client = new S3Client($args);
    //$this->cloudfront_url = $_ENV['AWS_CLOUDFRONT_DOMAIN'];
    //$this->cloudfront = new UrlSigner($_ENV['AWS_CLOUDFRONT_ACCESS_KEY_ID'], $_ENV['AWS_CLOUDFRONT_PRIVATE_KEY']);
  }

  protected function getFileKey($file, $args): string
  {
    $source = $file->transfer_id . '/' . $file->id;
    if (!$file?->transfer_id) {
      $source = $file->id . '/' . $file->name;
    }
    if (isset($args['chunk_number'])) {
      return $source . '/chunk_' . $args['chunk_number'];
    } else if (isset($args['extension'])) {
      return $source . '.' . $args['extension'];
    }
  }

  //protected function getSignedUrl($file): string
  //{
  //  $url = $this->cloudfront_url . '/' . $this->getFileKey($file, ['extension' => explode('/', $file->type)[1]]);
  //  $expires = strtotime(self::CF_EXPIRES_TIME);
  //  return $this->cloudfront->getSignedUrl($url, $expires);
  //}

  public function initSSH2() {
    $key = PublicKeyLoader::load(file_get_contents($_ENV['AWS_EC2_PRIVATE_KEY']));
    $ssh = new SSH2($_ENV['AWS_EC2_PUBLIC_IP']);
    if (!$ssh->login($_ENV['AWS_EC2_USERNAME'], $key)) {
        throw new \Exception('Login failed');
    }
    return $ssh;
  }

  public function __destruct()
  {
    Codes::endJob();
  }
}
