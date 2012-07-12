<?php

namespace Testes\Suite;
use Traversable;
use Testes\RunableInterface;

/**
 * Suite interface.
 * 
 * @category UnitTesting
 * @package  Testes
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
interface SuiteInterface extends RunableInterface
{
    /**
     * Adds a test to the suite.
     * 
     * @param RunableInterface $test The test to add.
     * 
     * @return SuiteInterface
     */
    public function addTest(RunableInterface $test);
    
    /**
     * Adds multiple tests to the suite.
     * 
     * @param Traversable $test Traversable item containing each test.
     * 
     * @return SuiteInterface
     */
    public function addTests(Traversable $tests);
}