<?php

namespace Hasdemir\Base\Exception;

/**
 * Http code 501
 */
class NotImplementException extends DefaultException
{
  protected $http_code = 501;

  public function __construct(string $message = '', $previous = null)
  {
    parent::__construct($message, $previous);
  }
}
