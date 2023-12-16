<?php

namespace Hasdemir\Base;

class Log
{
  protected static array $context = [];
  protected static string $message = '';
  protected static string $status = 'success';
  protected static array $error = [];

  const LOG_DIR = ROOT . DS . 'logs';

  public static function body()
  {
    return [
      'message' => self::$message,
      'context' => self::$context,
      'status' => self::$status,
      'error' => self::$error
    ];
  }

  public static function setContext(array $data)
  {
    self::$context = $data;
  }

  private static function insert($log, $type, $date = true)
  {
    if ($_ENV['APP_ENV'] === 'prod' && $type === 'daily') return;
    $log_file = $date ? date('Y-m-d') . ".log" : $type . ".log";
    if (!file_exists(self::LOG_DIR)) {
      mkdir(self::LOG_DIR);
    }
    if (!file_exists(self::LOG_DIR . DS . $type)) {
      mkdir(self::LOG_DIR . DS . $type);
    }

    $file = fopen(self::LOG_DIR . DS . $type . DS . $log_file, 'a');
    $write = fwrite($file, $log);
    fclose($file);
  }

  public static function daily($log)
  {
    self::insert($log, 'daily');
  }

  public static function startApp()
  {
    $log = '-------------------- App Started At => ' . date('Y-m-d H:i:s') .  ' --------------------/**/' . PHP_EOL;
    self::insert($log, 'daily');
  }

  public static function endApp()
  {
    self::sql();
    $execute_time = ((hrtime(true) - APP_START) / 1000000);
    $log = '-------------------- App Ended At => ' . date('Y-m-d H:i:s') .  ' ---------------------- Total Execute Time => ' . $execute_time . ' ms' . PHP_EOL . '[seperator]' . PHP_EOL;
    self::insert($log, 'daily');
  }

  public static function request($url, $method)
  {
    $log = '[' . date('Y-m-d H:i:s') . '] Request url => \'' . $url . '\', method => \'' . $method . '\'/**/' . PHP_EOL;
    self::insert($log, 'daily');
  }

  public static function error($response, $e, $th)
  {
    self::$error = [
      'message' => $response['message'],
      'th_message' => $e->getMessage(),
      'file' => $e->getFile(),
      'line' => $e->getLine()
    ];
    self::$status = 'error';

    $log = '[' . date('Y-m-d H:i:s') . '] Throwed error. Message => "' . $response['message'] . '", Status Code => \'' . (isset($e->http_code) ? $e->http_code : 500) . '\'' . PHP_EOL;
    self::insert($log, 'daily');

    $log = '[' . date('Y-m-d H:i:s') . '] {"message": "' . $e->getMessage() . '"';
    if (is_object($th)) {
      $log .= ', "th_mesage": "' . $th->getMessage() . '"';
    }
    $log .= ', "status": "' . (isset($e->http_code) ? $e->http_code : 500) . '", "file": "' . $e->getFile() . '", "line": "' . $e->getLine() . '"}';
    $log .= PHP_EOL . PHP_EOL;

    self::insert($log, 'error', false);
  }

  private static function sql()
  {
    $log = '[' . date('Y-m-d H:i:s') . '] SQL Queries => [' . PHP_EOL;
    foreach ($GLOBALS[Codes::SQL_QUERIES] as $item) {
      $log .= '                        Query => \'' . $item[Codes::QUERY] . '\'' . PHP_EOL;
      $log .= '                        Binds => [' . PHP_EOL;
      foreach ($item[Codes::BINDS] as $key => $value) {
        $log .= '                          \'' . $key . '\' => `' . $value . '`' . PHP_EOL;
      }
      $log .= '                        ]' . PHP_EOL;
    }
    $log .= '                      ]/**/' . PHP_EOL;
    self::insert($log, 'daily');
  }
}
