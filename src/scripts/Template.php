<?php

namespace Hasdemir\Base\Script;

class Template
{
  private static string $path = ROOT . DS . 'app' . DS;
  public static function make($name, $args)
  {
    if ($args && $name) {
      switch ($args[1]) {
        case 'controller':
          self::makeController($name);
          break;
        case 'model':
          self::makeModel($name);
          break;
        case 'db':
          self::makeDb($name);
          break;
        case 'all':
          self::makeController($name);
          self::makeModel($name);
          self::makeDb($name);
          break;

        default:
          echoLog('No argument passed', 'error');
          break;
      }
    }
  }

  public static function makeController($name)
  {
    $file = self::$path . CONTROLLER_FOLDER . DS . ucfirst($name) . 'Controller.php';
    $content = str_replace([
      '{{namespace}}',
      '{{class}}',
      '{{Class}}',
      '{{CLASS}}',
    ], [
      getControllerNamespace(),
      $name,
      classify($name) . 'Controller',
      strtoupper(classify($name)),
    ], file_get_contents(__DIR__ . '/templates/controller.txt'));
    $controller = fopen($file, 'x');

    if ($controller) {
      fwrite($controller, $content);
      fclose($controller);
      echoLog($file, 'success', ' created successfully' . PHP_EOL);
    } else echoLog($file, 'error', ' already exist' . PHP_EOL);
  }

  public static function makeModel($name)
  {
    if (MODEL_SUB_FOLDER === '') {
      $file = self::$path . MODEL_FOLDER . DS . classify($name) . '.php';
      $content = str_replace(
        ['{{namespace}}', '{{class}}', '{{Class}}'],
        [getModelNamespace(), $name, classify($name)],
        file_get_contents(__DIR__ . '/templates/model_pdo.txt')
      );
      $model = fopen($file, 'x');
      if ($model) {
        fwrite($model, $content);
        fclose($model);
        echoLog($file, 'success', ' created successfully' . PHP_EOL);
      } else echoLog($file, 'error', ' already exist' . PHP_EOL);
    } else {
      $folder = self::$path . MODEL_FOLDER . DS . classify($name);
      if (!file_exists($folder)) {
        mkdir($folder);
      }
      $folder = self::$path . MODEL_FOLDER . DS . classify($name) . DS . MODEL_SUB_FOLDER;
      if (!file_exists($folder)) {
        mkdir($folder);
      }

      $file = self::$path . MODEL_FOLDER . DS . classify($name) . DS . classify($name) . '.php';
      $content = str_replace(
        ['{{namespace}}', '{{sub}}', '{{class}}', '{{Class}}'],
        [getModelNamespace($name), getModelNamespace($name, true), $name, classify($name)],
        file_get_contents(__DIR__ . '/templates/model.txt')
      );
      $model = fopen($file, 'x');
      if ($model) {
        fwrite($model, $content);
        fclose($model);
        echoLog($file, 'success', ' created successfully');
      } else echoLog($file, 'error', ' already exist');

      $file = self::$path . MODEL_FOLDER . DS . classify($name) . DS . classify($name) . 'Search.php';
      $content = str_replace(
        ['{{namespace}}', '{{sub}}', '{{class}}', '{{Class}}'],
        [getModelNamespace($name), getModelNamespace($name, true), $name, classify($name)],
        file_get_contents(__DIR__ . '/templates/search.txt')
      );
      $search = fopen($file, 'x');
      if ($search) {
        fwrite($search, $content);
        fclose($search);
        echoLog($file, 'success', ' created successfully');
      } else echoLog($file, 'error', ' already exist');

      $file = self::$path . MODEL_FOLDER . DS . classify($name) . DS . classify($name) . 'Codes.php';
      $content = str_replace(
        ['{{namespace}}', '{{class}}', '{{Class}}'],
        [getModelNamespace($name), $name, classify($name)],
        file_get_contents(__DIR__ . '/templates/codes.txt')
      );
      $codes = fopen($file, 'x');
      if ($codes) {
        fwrite($codes, $content);
        fclose($codes);
        echoLog($file, 'success', ' created successfully');
      } else echoLog($file, 'error', ' already exist');

      $file = self::$path . MODEL_FOLDER . DS . classify($name) . DS . MODEL_SUB_FOLDER . DS . classify($name) . 'Redis.php';
      $content = str_replace(
        ['{{namespace}}', '{{Class}}'],
        [getModelNamespace($name, true), classify($name) . 'Redis'],
        file_get_contents(__DIR__ . '/templates/model_redis.txt')
      );
      $redis = fopen($file, 'x');
      if ($redis) {
        fwrite($redis, $content);
        fclose($redis);
        echoLog($file, 'success', ' created successfully');
      } else echoLog($file, 'error', ' already exist');

      $file = self::$path . MODEL_FOLDER . DS . classify($name) . DS . MODEL_SUB_FOLDER . DS . classify($name) . 'Pdo.php';
      $content = str_replace(
        ['{{namespace}}', '{{class}}', '{{Class}}'],
        [getModelNamespace($name, true), $name, classify($name) . 'Pdo'],
        file_get_contents(__DIR__ . '/templates/model_pdo.txt')
      );
      $pdo = fopen($file, 'x');
      if ($pdo) {
        fwrite($pdo, $content);
        fclose($pdo);
        echoLog($file, 'success', ' created successfully' . PHP_EOL);
      } else echoLog($file, 'error', ' already exist' . PHP_EOL);
    }
  }

  public static function makeDb($name)
  {
    $file_name = date("YmdHi") . '_' . VERSION . '_' . $name;
    $file = ROOT . DS . 'database' . DS . $file_name . '.php';

    $content = str_replace(
      ['{{Class}}', '{{class}}'],
      [classify($name) . implode('', explode('.', VERSION)), $name],
      file_get_contents(__DIR__ . '/templates/db.txt')
    );
    $db = fopen($file, 'x');
    if ($db) {
      fwrite($db, $content);
      fclose($db);
      echoLog($file, 'success', ' created successfully' . PHP_EOL);
    } else echoLog($file, 'error', ' already exist' . PHP_EOL);
  }
}
