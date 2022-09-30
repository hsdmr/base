<?php

namespace Hasdemir\Base;

use ArrayAccess;
use Countable;
use Iterator;

abstract class HandyClass implements ArrayAccess, Iterator, Countable
{

  protected array $container = [];
  protected array $keys = [];
  private int $position = 0;

  public function count(): int
  {
    return count($this->keys);
  }

  public function rewind(): void
  {
    $this->position = 0;
  }

  public function current(): mixed
  {
    return $this->container[$this->keys[$this->position]];
  }

  public function key(): mixed
  {
    return $this->keys[$this->position];
  }

  public function next(): void
  {
    echo ++$this->position;
  }

  public function valid(): bool
  {
    return isset($this->keys[$this->position]);
  }

  public function offsetSet($offset, $value): void
  {
    if (is_null($offset)) {
      $this->container[] = $value;
      $this->keys[] = array_key_last($this->container);
    } else {
      $this->container[$offset] = $value;
      if (!in_array($offset, $this->keys)) {
        $this->keys[] = $offset;
        $this->{$offset} = $value;
      }
    }
  }

  public function offsetExists($offset): bool
  {
    return isset($this->container[$offset]);
  }

  public function offsetUnset($offset): void
  {
    unset($this->container[$offset]);
    unset($this->keys[array_search($offset, $this->keys)]);
    unset($this->{$offset});
    $this->keys = array_values($this->keys);
  }

  public function offsetGet($offset): mixed
  {
    return isset($this->container[$offset]) ? $this->container[$offset] : '';
  }

  public function setContainer($params)
  {
    $this->container = $params;
    $this->keys = array_keys($this->container);
    return $this;
  }
}
