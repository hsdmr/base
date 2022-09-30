<?php

namespace Hasdemir\Base\Exception;

/**
 * Http code 406
 */
class UnexpectedValueException extends DefaultException
{
  public $http_code = 406;

  public function __construct(string $message, array $info = [], $previous = null)
  {
    parent::__construct($message, $info, $previous);
  }
}
