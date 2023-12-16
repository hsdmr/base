<?php

namespace Hasdemir\Base;

use stdClass;

class Crud  extends stdClass
{

  protected $storage = null;

  public function __construct()
  {
  }

  public function getProperties()
  {
    return get_object_vars($this);
  }

  public function __set($key, $value)
  {
    $this->setProperty($key, $value);
  }

  public function setProperty($key, $value)
  {
    $value = $this->checkValue($key, $value, $this);
    $this->{$key} = $value;
    return $this;
  }

    /**
     * This static method is used to check the value
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
  protected function checkValue($name, $value, $object = false)
  {
    return $value;
  }
}
