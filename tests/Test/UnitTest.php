<?php

namespace Test;
use Provider\UnitProvider;
use Testes\Test\UnitAbstract;

require_once __DIR__ . '/../Provider/UnitProvider.php';

class UnitTest extends UnitAbstract
{
    private $unit;
    
    public function setUp()
    {
        $this->unit = new UnitProvider;
        $this->unit->run();
    }
    
    public function assertions()
    {
        $passed = $this->unit->getAssertions()->getPassed();
        $failed = $this->unit->getAssertions()->getFailed();
        
        $this->assert(count($passed) === 1);
        $this->assert(count($failed) === 1);
    }
}