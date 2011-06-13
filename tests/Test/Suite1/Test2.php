<?php

namespace Test\Suite1;
use Testes\Test;

class Test2 extends Test
{
    public function test1()
    {
        $this->assert(true, 'True assertion.');
        $this->assert(false, 'False assertion.');
    }
    
    public function test2()
    {
        $this->assert(true, 'True assertion.');
        $this->assert(false, 'False assertion.');
    }
}