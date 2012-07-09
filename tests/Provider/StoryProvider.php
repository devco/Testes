<?php

namespace Provider;
use Testes\Test\StoryAbstract;

class StoryProvider extends StoryAbstract
{
    private $case;
    
    private $assert;
    
    public function trueAssertion()
    {
        $this->given('A test case', 'good')
             ->when('a test is run', true)
             ->then('assert');
    }
    
    public function falseAssertion()
    {
        $this->given('A test case', 'bad')
             ->when('a test is run', false)
             ->then('assert');
    }
    
    protected function givenATestCase($case)
    {
        $this->case = $case;
    }
    
    protected function whenATestIsRun($assert)
    {
        $this->assert = $assert;
    }
    
    protected function thenAssert()
    {
        $this->assert($this->assert, $this->case);
    }
}