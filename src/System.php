<?php

namespace Hasdemir\Base;

use PDO;
use stdClass;

class System
{
  const PDO = 'pdo';
  const REDIS = 'redis';
  public static object $pdo_instance;
  public static array $config_instance;
  public static object $redis_instance;

  public static function get($type)
  {
    switch ($type) {
      case self::PDO:
        $object = new PDO('mysql:host=' . MYSQL_HOST . '; dbname=' . MYSQL_NAME . ';port=' . MYSQL_PORT . '; charset=utf8', MYSQL_USER, MYSQL_PASSWORD);
        $object->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $object->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
        $object->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $object;

      case self::REDIS:
        //todo
        $object = new stdClass();
        return $object;
    }
  }

  public static function getPdo()
  {
    if (!isset(self::$pdo_instance)) {
      self::$pdo_instance = self::get(self::PDO);
    }
    return self::$pdo_instance;
  }

  public static function getRedis()
  {
    if (!isset(self::$redis_instance)) {
      self::$redis_instance = self::get(self::REDIS);
    }
    return self::$redis_instance;
  }
}
