<?php

namespace Testes\Suite;
use ArrayIterator;
use IteratorAggregate;
use Testes\Assertion\Set;
use Testes\RunableAbstract;
use Testes\RunableInterface;
use Traversable;

/**
 * Default suite implementation. Can be extended to provide setup for a particular set of tests in a matching directory.
 * 
 * @category UnitTesting
 * @package  Testes
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
class Suite extends RunableAbstract implements IteratorAggregate, SuiteInterface
{
    /**
     * The tests added to the suite.
     * 
     * @var array
     */
    private $tests = array();
    
    /**
     * Returns an iterator of all tests in the suite.
     * 
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return $this->getTests();
    }
    
    /**
     * Runs all tests.
     * 
     * @param RunableInterface $suite The test suite that ran the current test if any.
     * 
     * @return RunableAbstract
     */
    public function run(RunableInterface $suite = null)
    {
        $this->setUp();
        $this->startBenchmark();

        foreach ($this->tests as $test) {
            $test->run($this);
        }

        $this->stopBenchmark();
        $this->tearDown();

        return $this;
    }
    
    /**
     * Adds a single test to the suite.
     * 
     * @param RunableInterface $test A runable item to add.
     * 
     * @return RunableAbstract
     */
    public function addTest(RunableInterface $test)
    {
        $this->tests[] = $test;
        return $this;
    }
    
    /**
     * Adds a traversable set of tests.
     * 
     * @param Traversable $tests The tests to add.
     * 
     * @return RunableAbstract
     */
    public function addTests(Traversable $tests)
    {
        foreach ($tests as $test) {
            $this->addTest($test);
        }
        return $this;
    }
    
    /**
     * Returns all sub suites as a flat array iterator.
     * 
     * @return ArrayIterator
     */
    public function getSuites()
    {
        $suites = new ArrayIterator;

        foreach ($this->tests as $test) {
            if ($test instanceof SuiteInterface) {
                foreach ($test->getSuites() as $suite) {
                    $suites[] = $suite;
                }
            }
        }
        
        return $suites;
    }
    
    /**
     * Returns all sub tests as a flat array iterator.
     * 
     * @return ArrayIterator
     */
    public function getTests()
    {
        $tests = new ArrayIterator;
        foreach ($this->tests as $test) {
            if ($test instanceof SuiteInterface) {
                foreach ($test->getTests() as $subtest) {
                    $tests[] = $subtest;
                }
            } else {
                $tests[] = $test;
            }
        }
        return $tests;
    }
    
    /**
     * Counts all test recursively.
     * 
     * @return int
     */
    public function count()
    {
        return $this->getTests()->count();
    }
    
    /**
     * Returns all assertions recursively.
     * 
     * @return Set
     */
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
    
    /**
     * Returns all exceptions recursively.
     * 
     * @return ArrayIterator
     */
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