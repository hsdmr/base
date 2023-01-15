<?php

namespace Hasdemir\Base\Exception;

/**
 * Http code 401
 */
class AuthenticationException extends DefaultException
{
  protected $http_code = 401;

  public function __construct(string $message = '', $previous = null)
  {
    parent::__construct($message, $previous);
  }
}
