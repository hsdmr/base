<?php

namespace Hasdemir\Base;

use Exception;
use Hasdemir\Base\Exception\DefaultException;
use Hasdemir\Base\Exception\NotAllowedException;
use Hasdemir\Base\Exception\NotFoundException;
use Hasdemir\Base\Exception\NotImplementException;
use Hasdemir\Base\Exception\StoragePdoException;
use Hasdemir\Base\Exception\UnexpectedValueException;
use Throwable;

class App
{
  protected array $header = [];
  public Response $response;
  public Console $consol;
  public Route $route;
  public $GLOBALS;

  public function __construct($config = [])
  {
    $this->defineConfig($config);
    $GLOBALS[Codes::IS_ROUTE_CALLED] = false;
    $GLOBALS[Codes::SQL_QUERIES] = [];
    $this->response = new Response();
  }

  public function add($class, $isApi = true)
  {
    if (!class_exists($class)) {
      $class = classify(APP_NAME) . "\\" . classify(CONTROLLER_FOLDER) . "\\" . $class;
    }

    if (!method_exists($class, "routes")) {
      return false;
    }

    $routes = [];

    foreach (call_user_func([$class, "routes"]) as $route) {
      $routes[] = [$route[0], $isApi ? API_PREFIX : '' . $route[1], $route[2]];
    }

    $this->routeInstance()->addRoute($class, $routes);
  }

  public function run()
  {
    Log::startApp();
    try {
      $this->routeInstance()->run();
    } catch (Throwable $th) {
      $e = $th;
      $http_code = 500;
      switch (true) {
        case $e instanceof UnexpectedValueException:
        case $e instanceof NotFoundException:
        case $e instanceof StoragePdoException:
        case $e instanceof NotImplementException:
        case $e instanceof NotAllowedException:
        case $e instanceof DefaultException:
          $http_code = $e->http_code;
          break;
      }
      return $this->response->error($http_code, $this->header, $e->getMessage(), $e, $e->getPrevious());
    } finally {
      Log::endApp();
    }
  }

  public function console($argv = [])
  {
    try {
      $console = new Console();
      $console->run($argv);
    } catch (Exception $th) {
      echoLog($th->getMessage(), 'error', PHP_EOL);
    }
  }

  private function routeInstance()
  {
    if (!isset($this->route)) {
      $this->route = new Route(new Request());
    }
    return $this->route;
  }

  private function defineConfig($config)
  {
    define('APP_START', hrtime(true));
    define('ROOT', $config['ROOT']);
    define('DS', DIRECTORY_SEPARATOR);
    define('HTTP_OK', 200);
    define('HTTP_CREATED', 201);
    define('HTTP_NO_CONTENT', 204);

    define('MYSQL_HOST', $config['MYSQL_HOST'] ?? '127.0.0.1');
    define('MYSQL_PORT', $config['MYSQL_PORT'] ?? 3306);
    define('MYSQL_NAME', $config['MYSQL_NAME'] ?? 'hasdemir_app');
    define('MYSQL_USER', $config['MYSQL_USER'] ?? 'root');
    define('MYSQL_PASSWORD', $config['MYSQL_PASSWORD'] ?? '');

    define('APP_NAME', $config['APP_NAME'] ?? 'App');
    define('APP_ENV', $config['APP_ENV'] ?? 'dev');
    define('APP_URL', $config['APP_URL'] ?? 'http://localhost:8000');
    define('API_PREFIX', $config['API_PREFIX'] ?? '/api');
    define('VERSION', $config['VERSION'] ?? '0.1.0');
    define('MODEL_FOLDER', $config['MODEL_FOLDER'] ?? 'model');
    define('MODEL_SUB_FOLDER', $config['MODEL_SUB_FOLDER'] ?? 'storage');
    define('CONTROLLER_FOLDER', $config['CONTROLLER_FOLDER'] ?? 'controller');
  }
}
