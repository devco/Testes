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

    public function hashId()
    {
        $this->assert(
            $this->bob->hashId() === sha1(get_class($this->bob)),
            sprintf(
                'Unexpected hashId, expected %s got %s',
                sha1(get_class($this->bob)), $this->bob->hashId()
            )
        );
    }
}