<?php

namespace Hasdemir\Base\Exception;

/**
 * Http code 406
 */
class UnexpectedValueException extends DefaultException
{
  protected $http_code = 406;

  public function __construct(string $message = '', $previous = null)
  {
    parent::__construct($message, $previous);
  }
}
