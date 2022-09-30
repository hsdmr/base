<?php

namespace Hasdemir\Base;

class Crud
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

  protected function checkValue($name, $value, $object = false)
  {
    return $value;
  }
}
