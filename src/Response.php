<?php

namespace Hasdemir\Base;

class Response
{
  public static function error($http_code, $header, $message = null, $e = null, $th = null)
  {
    $response = [
      'message' => $message,
      'key' => $e->key ?? Codes::ERROR_UNKNOWN,
      'vars' => $e->vars ?? [],
    ];
    Log::error($response, $e, $th);
    return self::emit($http_code, $header, $response);
  }

  public static function emit($http_code, $header, $response)
  {
    $header['Api-Verison'] = VERSION;

    if (is_array($response)) {
      $header['Content-Type'] = 'application/json; charset=utf-8';
      $response = json_encode($response);
    } else {
      $header['Content-Type'] = $header['Content-Type'] ?? 'text/html; charset=utf-8';
    }

    foreach ($header as $key => $value) {
      header($key . ': ' . $value);
    }

    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, Total-Row');
    header('Access-Control-Expose-Headers: Total-Row');

    http_response_code($http_code);

    echo $response;
  }
}
