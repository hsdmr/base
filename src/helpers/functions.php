<?php

use Hasdemir\Base\Route;

if (!function_exists('randomString')) {
  function randomString(int $length = 60): string
  {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $random_string = '';
    for ($i = 0; $i < $length; $i++) {
      $random_string .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $random_string;
  }
}

if (!function_exists('classify')) {
  function classify($table): string
  {
    return implode('', array_map(fn ($item) => ucfirst($item), array_values(explode('_', $table))));
  }
}

if (!function_exists('getModelFromTable')) {
  function getModelFromTable($table): string
  {
    $subfolder = MODEL_SUB_FOLDER === '' ? classify($table) : "\\" . ucfirst(MODEL_SUB_FOLDER) . classify($table) . 'Pdo';
    $model = ucfirst(str_replace(' ', '', APP_NAME)) . "\\" . ucfirst(MODEL_FOLDER) . "\\" . $subfolder;
    return $model;
  }
}

if (!function_exists('getModelNamespace')) {
  function getModelNamespace($name = '', $storage = false): string
  {
    $name = $name === '' ? '' : "\\" . classify($name);
    $subfolder = !$storage ? '' : "\\" . ucfirst(MODEL_SUB_FOLDER);
    $namespace = ucfirst(str_replace(' ', '', APP_NAME)) . "\\" . ucfirst(MODEL_FOLDER) . $name . $subfolder;
    return $namespace;
  }
}

if (!function_exists('getControllerNamespace')) {
  function getControllerNamespace(): string
  {
    return ucfirst(str_replace(' ', '', APP_NAME)) . "\\" . ucfirst(CONTROLLER_FOLDER);
  }
}

if (!function_exists('redirect')) {
  function redirect($uri = null)
  {
    Route::redirect($uri);
  }
}

if (!function_exists('view')) {
  function view($view = null, $data = [])
  {
    $array = explode('.', $view);
    $extension = end($array);
    if ($extension === 'php' || $extension === 'html') {
      $last_index = key($array);
      unset($array[$last_index]);
    } else {
      $extension = 'php';
    }
    $view = implode(DS, $array);
    foreach ($data as $key => $value) {
      ${$key} = $value;
    }
    return include_once ROOT . DS . 'resources' . DS . 'views' . $view . '.' . $extension;
  }
}

if (!function_exists('asset')) {
  function asset($path = ''): void
  {
    echo $_ENV['APP_URL'] . '/' . $path;
  }
}

if (!function_exists('timestamps')) {
  function timestamps($soft_delete = false): string
  {
    $timestamps = "`created_at` BIGINT(20) NULL , `updated_at` BIGINT(20) NULL";
    $timestamps_with_delete = "`deleted_at` BIGINT(20) NULL , `created_at` BIGINT(20) NULL , `updated_at` BIGINT(20) NULL";

    if ($soft_delete) {
      return $timestamps_with_delete;
    } else {
      return $timestamps;
    }
  }
}

if (!function_exists('slugify')) {
  function slugify(string $text, string $divider = '-'): string
  {
    // replace non letter or digits by divider
    $text = preg_replace('~[^\pL\d]+~u', $divider, $text);
    // transliterate
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    // remove unwanted characters
    $text = preg_replace('~[^-\w]+~', '', $text);
    // trim
    $text = trim($text, $divider);
    // remove duplicate divider
    $text = preg_replace('~-+~', $divider, $text);
    // lowercase
    $text = strtolower($text);

    if (empty($text)) {
      return 'n-a';
    }

    return '/' . $text;
  }
}

if (!function_exists('primaryLanguageId')) {
  function primaryLanguageId(): int
  {
    return 1;
  }
}

if (!function_exists('currentLanguage')) {
  function currentLanguage(): string
  {
    return 'tr';
  }
}

if (!function_exists('getCallingMethodName')) {
  function getCallingMethodName(): string
  {
    return debug_backtrace()[2]['function'];
  }
}

if (!function_exists('echoLog')) {
  function echoLog($message1 = '', $status = '', $message2 = '')
  {
    $code = '';
    switch ($status) {
      case 'black':
        $code = "\033[30m";
        break;
      case 'error':
        $code = "\033[31m";
        break;
      case 'success':
        $code = "\033[32m";
        break;
      case 'warning':
        $code = "\033[33m";
        break;
      case 'primary':
        $code = "\033[34m";
        break;
      case 'secondary':
        $code = "\033[35m";
        break;
      case 'info':
        $code = "\033[36m";
        break;
      case 'white':
        $code = "\033[37m";
        break;
      default:
        $code = "\033[0m";
        break;
    }
    echo PHP_EOL . $code . /*"[" . date("Y-m-d H:i:s") . "]*/ "$message1 \033[0m" . $message2;
  }
}
