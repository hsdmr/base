<?php

namespace Hasdemir\Helper;

class Json
{
  public static function encode($value)
  {
    if (is_numeric($value) || is_null($value) || is_bool($value)) {
      return $value;
    } else {
      return json_encode($value);
    }
  }
  public static function decode($value)
  {
    if (is_numeric($value) || is_bool($value) || is_null($value)) {
      return $value;
    } else {
      $decoded_data = json_decode($value, true);
      return (json_last_error() ? $value : $decoded_data);
    }
  }
}
