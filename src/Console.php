<?php

namespace Hasdemir\Base;

use Hasdemir\Base\Script\Migration;
use Hasdemir\Base\Script\Template;

class Console
{
  public function run($argv)
  {
    $public_ROOT = ROOT . DS . 'public';
    $npm_ROOT = ROOT . DS . 'resources' . DS . 'js';
    $args = explode(':', $argv[1]);

    switch ($args[0]) {
      case 'serve':
        $exec = "(cd $public_ROOT && php -S localhost:8000)";
        exec($exec, $result);
        break;

      case 'dev':
        $exec = "(cd $npm_ROOT && npm run dev)";
        exec($exec, $result);
        break;

      case 'build':
        $exec = "(cd $npm_ROOT && npm run build)";
        exec($exec, $result);
        break;

      case 'migrate':
        Migration::run($args);
        break;

      case 'make':
        if (!isset($argv[2])) {
          echoLog('You need to specify the class name', 'error', PHP_EOL);
          return;
        }
        Template::make($argv[2], $args);
        break;

      default:
        echo 'No argumant passed';
    }
  }
}
