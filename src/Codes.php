<?php

namespace Hasdemir\Base;

class Codes
{
  const NAMESPACE = 'base';

  const IS_ROUTE_CALLED = 'is_route_called';

  const SQL_QUERIES = 'sql_queries';
  const QUERY = 'query';
  const BINDS = 'binds';

  const JOB_AUTH_ATTEMPT = 'auth_attempt';
  const JOB_SEARCH = 'search';
  const JOB_CREATE = 'create';
  const JOB_READ = 'read';
  const JOB_UPDATE = 'update';
  const JOB_DELETE = 'delete';
  const JOB_PDO = 'pdo';
  const JOB_REDIS = 'redis';

  const ERROR_PASSWORD_IS_INCORRECT = "'password' is incorrect";
  const ERROR_EMAIL_IS_WRONG = "'email' is wrong";
  const ERROR_USERNAME_IS_WRONG = "'username' is wrong";
  const ERROR_UNKNOWN = 'unknown_error';
  const ERROR_GENERIC_NOT_FOUND = 'generic_not_found';
  const ERROR_USER_DELETED = "This user deleted";
  const ERROR_KEY_ALREADY_REGISTERED = 'key_already_registered';
  const ERROR_URL_NOT_VALID = 'Url does not valid';
  const ERROR_URL_NOT_FOUND = 'Url does not exists';
  const ERROR_CALLED_FUNCTION_NOT_IMPLEMENTED = 'Called function not implemented';

  private static array $currentJob = [];
  private static int $code = 0;

  public static function currentJob($job)
  {
    $code = ++self::$code;
    $job = static::NAMESPACE . ':' . $job;
    self::$currentJob[] = [
      'job' => $job,
      'code' => $code
    ];
    Log::daily(['job' => $job, 'code' => $code]);
  }

  public static function endJob($job = null)
  {
    $job = $job ?? self::$currentJob[array_key_last(self::$currentJob)];
    unset(self::$currentJob[array_key_last(self::$currentJob)]);
    Log::daily($job);
  }
}
