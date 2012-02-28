<?php

namespace Test;
use Provider\UnitProvider;
use Testes\Test\Type\UnitAbstract;

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
        $ass = $this->unit->getAssertions();
        $this->assert($ass[0]->passed());
        $this->assert($ass[1]->failed());
    }
}