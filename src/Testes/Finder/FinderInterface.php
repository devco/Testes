<?php

namespace Testes\Finder;
use IteratorAggregate;
use Testes\Event;

interface FinderInterface extends IteratorAggregate
{
    public function run(Event\Test $event = null);
}