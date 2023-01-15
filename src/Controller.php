<?php

namespace Hasdemir\Base;

use Hasdemir\Base\Response;

class Controller
{
  protected static $routes = [];
  protected array $header = [];
  protected ?array $body = null;
  protected ?string $link = null;
  protected array $request_log = [];

  public function __construct($request, $args)
  {
    $GLOBALS[Codes::IS_ROUTE_CALLED] = true;
    $this->request_log = [
      'ip' => $request->ip,
      'port' => $request->port,
      'method' => $request->method,
      'path' => $request->path,
      'agent' => $request->agent,
      'headers' => logMask($request->headers),
      'body' => logMask($request->body),
      'get' => $request->params,
    ];
  }

  public static function routes(): array
  {
    return static::$routes;
  }

  public function response($http_code)
  {
    Log::setContext([
      'type' => 'API',
      'status' => 'success',
      'request' => $this->request_log,
      'response' => [
        'headers' => logMask($this->header),
        'body' => logMask($this->body)
      ]
    ]);
    Response::emit($http_code, $this->header, $this->body ?? '');
  }
}
