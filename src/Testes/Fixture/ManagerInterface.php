<?php

namespace Testes\Fixture;
use Countable;
use IteratorAggregate;

interface ManagerInterface extends Countable, IteratorAggregate
{
    public function init(FixtureInterface $fixture);

    public function install(FixtureInterface $fixture);

    public function uninstall(FixtureInterface $fixture);

    public function installed(FixtureInterface $fixture);

    public function installAll(array $fixtures);

    public function uninstallAll();
}