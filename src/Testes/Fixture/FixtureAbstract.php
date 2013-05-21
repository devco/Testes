<?php

namespace Testes\Fixture;
use ArrayObject;
use Traversable;

abstract class FixtureAbstract implements FixtureInterface
{
    const MAX_INT = 2147483647;

    private static $data = [];

    public function __construct()
    {
        $class = get_called_class();

        if (!isset(self::$data[$class])) {
            self::$data[$class] = new ArrayObject;
        }
    }

    public function offsetSet($name, $value)
    {
        $this->getData()->offsetSet($name, $value);
        return $this;
    }

    public function offsetGet($name)
    {
        $data = $this->getData();

        if ($data->offsetExists($name)) {
            return $data->offsetGet($name);
        }
    }

    public function offsetExists($name)
    {
        return $this->getData()->offsetExists($name);
    }

    public function offsetUnset($name)
    {
        $data = $this->getData();

        if ($data->offsetExists($name)) {
            $data->offsetUnset($name);
        }

        return $this;
    }

    public function count()
    {
        return $this->getData()->count();
    }

    public function getIterator()
    {
        return $this->getData();
    }

    public function setData($data)
    {
        if ($data instanceof Traversable) {
            $data = iterator_to_array($data);
        }

        $this->getData()->exchangeArray($data);

        return $this;
    }

    public function getData()
    {
        return self::$data[get_called_class()];
    }

    public function toArray()
    {
        return $this->getData()->getArrayCopy();
    }

    public function id()
    {
        return (string) ((crc32(get_class($this)) - self::MAX_INT) * -1) ?: 1;
    }
}