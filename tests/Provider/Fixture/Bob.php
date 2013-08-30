<?php

namespace Provider\Fixture;
use Testes\Fixture\FixtureAbstract;

class Bob extends FixtureAbstract
{
    public function init(Address $address)
    {
        $this['id']      = md5(rand() . microtime() . rand());
        $this['name']    = 'Bob Bobberson';
        $this['address'] = $address['id'];
    }

    public function install()
    {

    }

    public function uninstall()
    {

    }

    public function installed()
    {
        return true;
    }
}