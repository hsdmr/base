<?php

namespace Hasdemir\Base\Exception;

/**
 * Http code 501
 */
class NotImplementException extends DefaultException
{
  public $http_code = 501;

  public function __construct(string $message, array $info = [], $previous = null)
  {
    parent::__construct($message, $info, $previous);
  }
}
