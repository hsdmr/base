<?php

namespace Hasdemir\Base;

use Hasdemir\Base\Response;

class Controller
{
  protected static $routes = [];
  protected array $header = [];
  protected ?array $body = null;
  protected ?string $link = null;

  public function __construct()
  {
    $GLOBALS[Codes::IS_ROUTE_CALLED] = true;
  }

  public static function routes(): array
  {
    return static::$routes;
  }

  public function response($http_code)
  {
    Response::emit($http_code, $this->header, $this->body ?? '');
  }
}
