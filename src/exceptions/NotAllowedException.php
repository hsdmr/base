<?php

namespace Hasdemir\Base\Exception;

/**
 * Http code 405
 */
class NotAllowedException extends DefaultException
{
  public $http_code = 405;

  public function __construct(string $message, array $info = [], $previous = null)
  {
    parent::__construct($message, $info, $previous);
  }
}
