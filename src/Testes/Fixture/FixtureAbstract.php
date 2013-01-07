<?php

namespace Testes\Fixture;
use ArrayIterator;

abstract class FixtureAbstract implements FixtureInterface
{
    private $data = [];

    private $required = [];

    public function offsetSet($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function offsetGet($name)
    {
        if (isset($this->data[$name])) {
            return $this->data[$name];
        }
    }

    public function offsetExists($name)
    {
        return isset($this->data[$name]);
    }

    public function offsetUnset($name)
    {
        if (isset($this->data[$name])) {
            unset($this->data[$name]);
        }
    }

    public function getIterator()
    {
        return new ArrayIterator($this->data);
    }

    public function data()
    {
        return $this->data;
    }
}