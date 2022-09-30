<?php

namespace Hasdemir\Base;

class Session
{
  protected const FLASH_KEY = 'flashes';
  private static $instance;

  public function __construct()
  {
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }
  }

  public static function getInstance()
  {
    if (!isset(self::$instance)) {
      self::$instance = new Session();
    }
    return self::$instance;
  }

  public function setFlash($key, $message)
  {
    $_SESSION[self::FLASH_KEY][$key] = $message;
  }

  public function getFlash($key)
  {
    return $_SESSION[self::FLASH_KEY][$key] ?? false;
  }

  public function set($key, $value)
  {
    $_SESSION[$key] = $value;
  }

  public function get($key)
  {
    return $_SESSION[$key] ?? false;
  }

  public function remove($key)
  {
    if (isset($_SESSION[$key])) {
      unset($_SESSION[$key]);
    }
  }

  public function flush()
  {
    session_destroy();
  }

  public function __destruct()
  {
    unset($_SESSION[self::FLASH_KEY]);
  }
}
