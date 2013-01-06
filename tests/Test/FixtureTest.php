<?php

namespace Test;
use Provider\Fixture\Bob;
use Testes\Test\UnitAbstract;

class FixtureTest extends UnitAbstract
{
    public function setUp()
    {
        $this->bob = new Bob;
    }

    public function adding()
    {
        $this->assert($this->bob instanceof Bob, 'The fixutre was not added.');
    }

    public function requirements()
    {
        $this->assert($this->bob['address'], 'Bob does not have an address.');
    }
}