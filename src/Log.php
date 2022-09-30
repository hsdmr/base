<?php

namespace Hasdemir\Base;

class Log
{
  const LOG_DIR = ROOT . DS . 'logs';
  private static array $currentJob = [];
  private static int $code = 0;

  private static function insert($log, $type, $date = true)
  {
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

  public static function daily($job)
  {
    $log = '[' . date('Y-m-d H:i:s') . '] End Job => \'' . ($job['job']) . '\' [=== ' . ($job['code']) . ' ===]' . PHP_EOL;
    self::insert($log, 'daily');
  }

  public static function startApp()
  {
    $log = '-------------------- App Started At => ' . date('Y-m-d H:i:s') .  ' --------------------' . PHP_EOL;
    self::insert($log, 'daily');
  }

  public static function endApp()
  {
    self::sql();
    $log = '-------------------- App Ended At => ' . date('Y-m-d H:i:s') .  ' ----------------------' . PHP_EOL . '[seperator]' . PHP_EOL;
    self::insert($log, 'daily');
  }

  public static function currentJob($job)
  {
    $code = ++self::$code;
    self::$currentJob[] = [
      'job' => $job,
      'code' => $code
    ];
    $log = '[' . date('Y-m-d H:i:s') . '] Start Job => \'' . ($job) . '\' [=== ' . ($code) . ' ===]' . PHP_EOL;
    self::insert($log, 'daily');
  }

  public static function endJob($job = null)
  {
    $job = $job ?? self::$currentJob[array_key_last(self::$currentJob)];
    unset(self::$currentJob[array_key_last(self::$currentJob)]);
    $log = '[' . date('Y-m-d H:i:s') . '] End Job => \'' . ($job['job']) . '\' [=== ' . ($job['code']) . ' ===]' . PHP_EOL;
    self::insert($log, 'daily');
  }

  public static function request($url, $method)
  {
    $log = '[' . date('Y-m-d H:i:s') . '] Request url => \'' . $url . '\', method => \'' . $method . '\'' . PHP_EOL;
    self::insert($log, 'daily');
  }

  public static function error($response, $e, $th)
  {
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
    $log = '[' . date('Y-m-d H:i:s') . '] SQL_Query => [' . PHP_EOL;
    foreach ($GLOBALS[Codes::SQL_QUERIES] as $item) {
      $log .= '                        Query => \'' . $item[Codes::QUERY] . '\'' . PHP_EOL;
      $log .= '                        Binds => [' . PHP_EOL;
      foreach ($item[Codes::BINDS] as $key => $value) {
        $log .= '                          \'' . $key . '\' => \'' . $value . '\'' . PHP_EOL;
      }
      $log .= '                        ]' . PHP_EOL;
    }
    $log .= '                      ]' . PHP_EOL;
    self::insert($log, 'daily');
  }
}
