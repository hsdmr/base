<?php

namespace Hasdemir\Base\Exception;

/**
 * Http code 401
 */
class AuthenticationException extends DefaultException
{
  public $http_code = 401;

  public function __construct(string $message, array $info = [], $previous = null)
  {
    parent::__construct($message, $info, $previous);
  }
}
