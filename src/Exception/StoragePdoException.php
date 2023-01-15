<?php

namespace Hasdemir\Base\Exception;

/**
 * Http code 503
 */
class StoragePdoException extends DefaultException
{
  protected $http_code = 503;

  public function __construct(string $message = '', $previous = null)
  {
    parent::__construct($message, $previous);
  }
}
