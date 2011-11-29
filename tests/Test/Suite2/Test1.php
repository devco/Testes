<?php

namespace Test\Suite2;
use Testes\Test\Test;

class Test1 extends Test
{
    public function test1()
    {
        $this->assert(true, 'True assertion.');
        $this->assert(false, 'False assertion.');
    }
    
    public function test2()
    {
        
    }
}