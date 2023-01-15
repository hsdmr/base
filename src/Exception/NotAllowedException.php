<?php

namespace Hasdemir\Base\Exception;

/**
 * Http code 405
 */
class NotAllowedException extends DefaultException
{
  protected $http_code = 405;

  public function __construct(string $message = '', $previous = null)
  {
    parent::__construct($message, $previous);
  }
}
