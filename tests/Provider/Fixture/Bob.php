<?php

namespace Provider\Fixture;
use Testes\Fixture\FixtureAbstract;

class Bob extends FixtureAbstract
{
    public function setUp(Address $address)
    {
        $this['id']      = md5(rand() . microtime() . rand());
        $this['name']    = 'Bob Bobberson';
        $this['address'] = $address['id'];
    }

    public function tearDown()
    {

    }
}