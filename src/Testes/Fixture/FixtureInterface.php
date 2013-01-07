<?php

namespace Testes\Fixture;
use ArrayAccess;
use IteratorAggregate;

interface FixtureInterface extends ArrayAccess, IteratorAggregate
{
    public function setData(array $data);

    public function getData();
}