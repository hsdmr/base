<?php

namespace Hasdemir\Base;

use Sinergi\BrowserDetector\Browser;
use Sinergi\BrowserDetector\Device;
use Sinergi\BrowserDetector\Os;
use Sinergi\BrowserDetector\Language;

class Request
{
  protected string $dir;
  protected string $base;
  public string $uri;
  public string $schema;
  public string $host;
  public string $port;
  public string $path;
  public string $method;
  public string $ip;
  public array $agent;
  public array $headers;
  public string $body;
  public array $params;

  public function __construct()
  {
    $this->dir = dirname($_SERVER['SCRIPT_NAME']);
    $this->base = basename($_SERVER['SCRIPT_NAME']);
    $this->schema = $_SERVER['REQUEST_SCHEME'];
    $this->host = $_SERVER['HTTP_HOST'];
    $this->uri = ($this->schema . '://' . ($_SERVER['APP_URL'] ??  $this->host)) . $this->uri();
    $this->path = $this->path();
    $this->method = $this->method();
    $this->ip = $_SERVER['REMOTE_ADDR'];
    $this->port = $_SERVER['REMOTE_PORT'];
    $this->agent = $this->agent();
    $this->headers = $this->headers();
    $this->body = $this->body();
    $this->params = $this->params();
    Log::request($this->uri(), $this->method());
  }

  public function uri()
  {
    $uri = str_replace($this->dir . $this->base, '', $_SERVER['REQUEST_URI']);
    if (substr($uri, 0, 1) != '/') {
      $uri = '/' . $uri;
    }
    return $uri;
  }

  public function path()
  {
    return explode('?', $this->uri())[0];
  }

  public function method()
  {
    return strtoupper($_SERVER['REQUEST_METHOD']);
  }

  public function agent()
  {
    $browser = new Browser();
    $os = new Os();
    $device = new Device();
    $language = new Language();

    return [
      'browser' => $browser->getName(),
      'version' => $browser->getVersion(),
      'os' => $os->getName(),
      'device' => $device->getName(),
      'language' => $language->getLanguage(),
    ];
  }

  public function headers($header = '')
  {
    $headers = getallheaders();

    if ($header) {
      return $headers[$header];
    }

    return $headers;
  }

  public function body()
  {
    $body = [];
    if ($this->method() === 'POST' || $this->method() === 'PUT' || $this->method() === 'PATCH') {
      $body = file_get_contents('php://input');
    }
    return $body;
  }

  public function params()
  {
    $params = [];
    foreach ($_REQUEST ?? [] as $key => $value) {
      if (is_numeric($value)) {
        $params[$key] = (int) filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);
      } else if ($value === 'true') {
        $params[$key] = true;
      } else if ($value === 'false') {
        $params[$key] = false;
      } else {
        $params[$key] = (string) filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);
      }
    }

    return $params;
  }

  public function isValid(): bool
  {
    return preg_match('@^([-a-zA-Z0-9%.=_#?&//]*)$@', $this->uri());
  }
}
