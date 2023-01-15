<?php

namespace Hasdemir\Base\Exception;

/**
 * Http code 404
 */
class NotFoundException extends DefaultException
{
  protected $http_code = 404;

  public function __construct(string $message = '', $previous = null)
  {
    parent::__construct($message, $previous);
  }
}
