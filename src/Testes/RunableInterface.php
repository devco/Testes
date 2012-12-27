<?php

namespace Testes;
use Countable;
use Testes\Assertion\AssertionInterface;
use Traversable;

interface RunableInterface extends Countable
{
    public function run(callable $after = null);

    public function setUp();

    public function tearDown();

    public function getAssertions();

    public function getExceptions();

    public function getBenchmarks();
}