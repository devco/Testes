<?php

namespace Testes;
use Countable;
use Testes\Assertion\AssertionInterface;
use Traversable;

interface RunableInterface extends Countable
{
    public function run(Event\Test $event = null);

    public function setUp();

    public function tearDown();

    public function isPassed();

    public function isFailed();

    public function getAssertions();

    public function getExceptions();

    public function getBenchmarks();
}