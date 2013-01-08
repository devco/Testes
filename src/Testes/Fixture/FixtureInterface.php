<?php

namespace Testes\Fixture;
use ArrayAccess;
use Countable;
use IteratorAggregate;

interface FixtureInterface extends ArrayAccess, Countable, IteratorAggregate
{
    public function setData($data);

    public function getData();
}