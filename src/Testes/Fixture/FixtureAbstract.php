<?php

namespace Testes\Fixture;

abstract class FixtureAbstract implements FixtureInterface
{
    private $data = [];

    protected static $staticData = [];

    public function __construct()
    {
        $this->data = static::data();
    }

    public function __get($name)
    {
        if (isset($this->data[$name])) {
            return $this->data[$name];
        }
    }

    public static function data()
    {
        if (isset($this)) {
            return $this->data;
        }

        $class = get_called_class();

        if (!isset(static::$staticData[$class])) {
            static::$staticData[$class] = static::generateData();
        }

        return static::$staticData[$class];
    }

    public function setUp()
    {

    }

    public function tearDown()
    {

    }

    public static function __callStatic($name, array $args = [])
    {
        $data = static::data();

        if (isset($data[$name])) {
            return $data[$name];
        }
    }

    abstract protected static function generateData();
}