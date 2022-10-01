<?php

namespace Hasdemir\Base\Exception;

/**
 * Http code 403
 */
class ForbiddenException extends DefaultException
{
  protected $http_code = 403;

  public function __construct(string $message = '', $previous = null)
  {
    parent::__construct($message, $previous);
  }
}
