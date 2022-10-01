<?php

namespace Hasdemir\Base;

class RedisModel
{
  protected string $key = '';

  public function __construct()
  {
    Codes::currentJob($this->key . '-redis');
    $this->redis = System::getRedis();
  }

  public function __destruct()
  {
    Codes::endJob();
  }
}
