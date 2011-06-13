<?php

namespace Test\Suite1;
use Testes\Test;

class Test1 extends Test
{
    public function test1()
    {
        $this->assert(true, 'True assertion.', 1);
        $this->assert(false, 'False assertion.', 2);
    }
    
    public function test2()
    {
        $this->assert(true, 'True assertion.', 3);
        $this->assert(false, 'False assertion.', 4);
    }
}