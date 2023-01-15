<?php

namespace Hasdemir\Base\Exception;

/**
 * Http code 500
 */
class DefaultException extends \Exception
{
  protected $http_code = 500;

  public function __construct(string $message = '', $previous = null)
  {
    parent::__construct($message, $this->http_code, $previous);
  }
}
