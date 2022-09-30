<?php

namespace Hasdemir\Base\Exception;

/**
 * Http code 500
 */
class DefaultException extends \Exception
{
  public $http_code = 500;

  public function __construct(string $message, array $info = [], $previous = null)
  {
    foreach ($info as $key => $value) {
      $this->{$key} = $value;
    }
    parent::__construct($message, $this->http_code, $previous);
  }
}
