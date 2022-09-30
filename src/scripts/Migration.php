<?php

namespace Hasdemir\Base\Script;

use Hasdemir\Base\System;
use Throwable;

class Migration
{
  public static \PDO $pdo;

  public static function run($args = [])
  {
    self::$pdo = System::getPdo();

    $files = scandir(ROOT . DS . 'database');

    if (isset($args[1])) {
      if ($args[1] === 'fresh') {
        $statement = self::$pdo->prepare("SHOW tables");
        if ($statement->execute()) {
          $tables = $statement->fetchAll(\PDO::FETCH_ASSOC);
          $key = "Tables_in_" . MYSQL_NAME;
          foreach ($tables as $table) {
            self::$pdo->exec("DROP TABLE `{$table[$key]}`");
          }
          echoLog("Dropped all tables", 'success');
        }
      }
    }

    self::createMigrationsTable();
    $appliedMigrations = self::getAppliedMigrations();
    $batch = self::getLastBatch();

    $newMigrations = [];
    $files = scandir(ROOT . DS . 'database');

    $toApplyMigrations = array_diff($files, $appliedMigrations);

    try {
      foreach ($toApplyMigrations as $migration) {
        if ($migration === '.' || $migration === '..') {
          continue;
        }

        require_once ROOT . DS . 'database' . DS . $migration;
        $name = explode('_', pathinfo($migration, PATHINFO_FILENAME));
        $version = $name[1];
        unset($name[0]);
        unset($name[1]);
        $className = classify(implode('_', $name)) . implode('', explode('.', $version));
        $instance = new $className();
        echoLog("Migrating", 'warning', $migration);
        $instance->up();
        echoLog("Migrated", 'success', $migration);
        $newMigrations[] = $migration . "', '" . $version . "', '" . $batch . "', '" . time();
      }

      if (!empty($newMigrations)) {
        self::saveMigrations($newMigrations);
      }
      echo PHP_EOL;
      echoLog("All migrations are applied", 'success', PHP_EOL);
    } catch (Throwable $th) {
      echoLog($th->getMessage(), 'error', PHP_EOL);
      echoLog('Please make sure there is no error!', 'secondary', PHP_EOL);
    }
  }

  private static function createMigrationsTable()
  {
    self::$pdo->exec("CREATE TABLE IF NOT EXISTS migration (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    migration_name VARCHAR(50), `version` VARCHAR(20), `batch` INTEGER(11),
                    created_at BIGINT(20) NULL
                )  ENGINE=INNODB;");
  }

  private static function getLastBatch()
  {
    $statement = self::$pdo->prepare("SELECT batch FROM migration ORDER BY `batch` DESC LIMIT 1");
    $statement->execute();
    $table = $statement->fetch(\PDO::FETCH_ASSOC);

    return $table ? $table['batch'] + 1 : 1;
  }

  private static function getAppliedMigrations()
  {
    $statement = self::$pdo->prepare("SELECT migration_name FROM migration");
    $statement->execute();

    return $statement->fetchAll(\PDO::FETCH_COLUMN);;
  }

  private static function saveMigrations(array $newMigrations)
  {
    $str = implode(',', array_map(fn ($m) => "('$m')", $newMigrations));
    $statement = self::$pdo->prepare("INSERT INTO migration (`migration_name`, `version`, `batch`, `created_at`) VALUES 
        $str
    ");
    $statement->execute();
  }
}
