<?php

namespace Hasdemir\Base;

use Hasdemir\Base\Exception\AuthenticationException;
use PDO;

class Auth
{
  private $db;
  private static $instance;

  public function __construct()
  {
    $this->db = System::getPdo();
  }

  public static function getInstance()
  {
    if (!isset(self::$instance)) {
      self::$instance = new Auth();
    }
    return self::$instance;
  }

  public function attempt($credentials)
  {
    Codes::currentJob(Codes::JOB_AUTH_ATTEMPT);
    try {
      $key = 'username';

      if (filter_var($credentials['user'], FILTER_VALIDATE_EMAIL)) {
        $key = 'email';
      }

      $sql = "SELECT * FROM user WHERE $key = :$key";
      $statement = $this->db->prepare($sql);
      $statement->bindValue(":$key", $credentials['user']);
      $GLOBALS[Codes::SQL_QUERIES][] = [
        Codes::QUERY => $sql,
        Codes::BINDS => [$key => $credentials['user']]
      ];
      $statement->execute();
      $user = $statement->fetch(PDO::FETCH_ASSOC);

      if (!$user) {
        switch ($key) {
          case 'username':
            throw new AuthenticationException("'username' is wrong", Codes::key(Codes::ERROR_USERNAME_IS_WRONG));

          case 'email':
            throw new AuthenticationException("'email' is wrong", Codes::key(Codes::ERROR_EMAIL_IS_WRONG));
        }
      }

      if ($user['deleted_at'] != null) {
        throw new AuthenticationException("This user deleted", Codes::key(Codes::ERROR_USER_DELETED));
      }

      if (!password_verify($_POST['password'], $user['password'])) {
        throw new AuthenticationException("'password' is incorrect", Codes::key(Codes::ERROR_PASSWORD_IS_INCORRECT));
      }

      Session::getInstance()->set('user', $user);
      return true;
    } finally {
      Codes::endJob();
    }
  }
}
