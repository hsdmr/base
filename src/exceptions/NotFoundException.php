<?php

namespace Hasdemir\Base\Exception;

/**
 * Http code 404
 */
class NotFoundException extends DefaultException
{
  public $http_code = 404;

  public function __construct(string $message, array $info = [], $previous = null)
  {
    parent::__construct($message, $info, $previous);
  }
}
