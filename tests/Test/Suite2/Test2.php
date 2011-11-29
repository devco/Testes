<?php

namespace Test\Suite2;
use Testes\Test\Test;

class Test2 extends Test
{
    public function test1()
    {
        
    }
    
    public function test2()
    {
        $this->assert(true, 'True assertion.');
        $this->assert(false, 'False assertion.');
    }
}