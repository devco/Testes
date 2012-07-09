<?php

namespace Provider;
use Testes\Test\UnitAbstract;

class UnitProvider extends UnitAbstract
{
    public function trueAssertion()
    {
        $this->assert(true);
    }
    
    public function falseAssertion()
    {
        $this->assert(false);
    }
}