<?php

namespace Hasdemir\Base;

use Hasdemir\Base\Exception\ForbiddenException;
use Hasdemir\Base\Exception\NotFoundException;
use Hasdemir\Base\Exception\NotImplementException;

class Route
{
  public static $hasRoute = false;
  public static $args_keys = [];
  public array $routes = [];
  public Request $request;

  public function __construct($request)
  {
    $this->request = $request;
  }

  public static function pattern(&$uri, $reqest_path)
  {
    $patterns = [];
    self::$args_keys = [];
    $reqest_path_array = explode('/', $reqest_path);
    $last_key = array_key_last($reqest_path_array);
    unset($reqest_path_array[0]);
    if ($reqest_path_array[$last_key] === '') {
      unset($reqest_path_array[$last_key]);
    }
    $length = count($reqest_path_array);
    $uri_array = explode('/', $uri);

    $i = 0;
    foreach ($uri_array as $item) {
      if (str_contains($item, '?}')) {
        if (count($patterns) < $length) {
          $patterns[$item] = '([0-9A-Za-z-]+)';
          self::$args_keys[] = trim($item, '{}');
        } else {
          unset($uri_array[$i]);
        }
      } elseif (str_contains($item, '}')) {
        $patterns[$item] = '([0-9A-Za-z-]+)';
        self::$args_keys[] = trim($item, '{}');
      }
      $i++;
    }
    $uri = implode('/', $uri_array);
    return $patterns;
  }

  public function run()
  {
    if ($this->request->method() === 'OPTIONS') {
      Response::emit(HTTP_NO_CONTENT, [], '');
      die;
    }

    if (!$this->request->isValid()) {
      if ($this->isApi()) {
        throw new ForbiddenException(Codes::ERROR_URL_NOT_VALID);
      } else {
        include_once 'html/403.php';
      }
    }

    $this->handle();

    self::hasRoute($this->isApi());
  }

  public static function hasRoute($is_api)
  {
    if (self::$hasRoute === false) {
      if ($is_api) {
        throw new NotFoundException(Codes::ERROR_URL_NOT_FOUND);
      } else {
        include_once 'html/404.php';
      }
    }
  }

  public function isApi(): bool
  {
    $exploded = explode('/', $this->request->path());
    if (isset($exploded[1])) {
      return '/' . explode('/', $this->request->path())[1] === API_PREFIX;
    }
    return false;
  }

  public function handle(string $class_suffix = 'Controller')
  {
    foreach ($this->routes as $key => $value) {
      foreach ($value as $route) {
        $class = $key . $class_suffix;
        if (!class_exists($class)) {
          $class = getControllerNamespace() . $class;
        }
        $method = $route[0];
        $uri = $route[1];
        $function = $route[2];
        $reqest_path = $this->request->path();
        $reqest_method = $this->request->method();
        $pattern = self::pattern($uri, $reqest_path);
        $uri = str_replace(array_keys($pattern), array_values($pattern), $uri);

        if ((preg_match('@^' . $uri . '$@', $reqest_path, $matches) || preg_match('@^' . $uri . '$@', $reqest_path . '/', $matches)) && $method == $reqest_method) {
          self::$hasRoute = true;

          if (method_exists($class, $function)) {
            call_user_func_array([new $class($this->request, $this->prepareArgs($matches)), $function], [$this->request, $this->prepareArgs($matches)]);
          }
          if (!$GLOBALS[Codes::IS_ROUTE_CALLED] && $this->isApi()) {
            throw new NotImplementException(Codes::ERROR_CALLED_FUNCTION_NOT_IMPLEMENTED);
          }
          if (!$GLOBALS[Codes::IS_ROUTE_CALLED] && !$this->isApi()) {
            include_once 'html/404.php';
          }
          break 2;
        }
      }
    }
  }

  public static function redirect($url)
  {
    header('Location: ' . $url);
    return;
  }

  public function addRoute($class, $routes)
  {
    $this->routes[$class] = $routes;
  }

  private function prepareArgs($args)
  {
    unset($args[0]);
    return array_combine(self::$args_keys, $args);
  }
}
