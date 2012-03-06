<?php

namespace Testes\Suite;
use ArrayIterator;
use IteratorAggregate;
use Testes\Assertion\Set;
use Testes\RunableAbstract;
use Testes\RunableInterface;
use Traversable;

class Suite extends RunableAbstract implements IteratorAggregate, SuiteInterface
{
    private $tests = array();
    
    public function getIterator()
    {
        return $this->getTests();
    }
    
    public function run()
    {
        $this->setUp();
        $this->startBenchmark();
        foreach ($this->tests as $test) {
            $test->run();
        }
        $this->stopBenchmark();
        $this->tearDown();
        return $this;
    }
    
    public function addTest(RunableInterface $test)
    {
        $this->tests[] = $test;
        return $this;
    }
    
    public function addTests(Traversable $tests)
    {
        foreach ($tests as $test) {
            $this->addTest($test);
        }
        return $this;
    }
    
    public function getTests()
    {
        return new ArrayIterator($this->tests);
    }
    
    public function count()
    {
        return count($this->tests);
    }
    
    public function getAssertions()
    {
        $assertions = new Set;
        foreach ($this->tests as $test) {
            foreach ($test->getAssertions() as $assertion) {
                $assertions->add($assertion);
            }
        }
        return $assertions;
    }
    
    public function getExceptions()
    {
        $exceptions = new ArrayIterator;
        foreach ($this->tests as $test) {
            foreach ($test->getExceptions() as $exception) {
                $exceptions[] = $exception;
            }
        }
        return $exceptions;
    }
}