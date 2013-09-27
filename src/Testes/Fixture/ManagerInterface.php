<?php

namespace Testes\Fixture;
use Countable;
use IteratorAggregate;

interface ManagerInterface extends Countable, IteratorAggregate
{
    public function set($name, FixtureInterface $fixture);

    public function get($name);

    public function has($name);

    public function remove($name);

    public function install();

    public function uninstall();
}