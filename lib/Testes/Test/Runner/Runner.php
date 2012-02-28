<?php

namespace Testes\Test\Runner;
use Testes\Test\Finder\FinderInterface;
use Testes\Test\Reporter\ReporterInterface;

/**
 * Test runner.
 * 
 * @category UnitTesting
 * @package  Testes
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
class Runner
{
    private $reporter;
    
    public function __construct(ReporterInterface $reporter)
    {
        $this->reporter = $reporter;
    }
    
	/**
     * Runs all tests in the suite.
     * 
     * @param \Testes\Test\FinderInterface $tests The test locator.
     * 
     * @return \Testes\Test\Runner
     */
    public function run(FinderInterface $tests)
    {
        $this->reporter->startMemoryCounter();
        $this->reporter->startTimer();
        foreach ($tests as $test) {
            try {
                $test->run();
                $this->reporter->addAssertions($test->getAssertions());
            } catch (\Exception $e) {
                $this->reporter->addException($e);
                $test->tearDown();
            }
        }
        $this->reporter->stopTimer();
        $this->reporter->stopMemoryCounter();
        return $this;
    }
}