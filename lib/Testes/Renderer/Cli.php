<?php

namespace Testes\Renderer;
use Testes\Test\Reporter\ReporterInterface;

/**
 * Renders the test output in command-line format.
 * 
 * @category UnitTesting
 * @package  Testes
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
class Cli implements RendererInterface
{
    /**
     * Renders the test results.
     * 
     * @param TestInterface $test The test to output.
     * 
     * @return string
     */
    public function render(ReporterInterface $test)
    {
        $str  = $this->renderAssertions($test);
        $str .= PHP_EOL;
        
        if ($exceptions = $this->renderExceptions($test)) {
            $str .= PHP_EOL;
            $str .= $exceptions;
            $str .= PHP_EOL;
        }

        $str .= PHP_EOL;
        $str .= $this->renderTime($test);
        $str .= PHP_EOL;
        
        return $str;
    }
    
    /**
     * Renders the assertions in the test.
     * 
     * @param TestInterface $test The test to output.
     * 
     * @return string
     */
    public function renderAssertions(ReporterInterface $test)
    {
        $str = '';
        if ($assertions = $test->getFailedAssertions()) {
            $str .= 'Failed' . PHP_EOL;
            $str .= '------' . PHP_EOL;
            foreach ($assertions as $assertion) {
                $str .= $assertion->getTestClass()
                     .  '->'
                     .  $assertion->getTestMethod()
                     .  '() in '
                     .  $assertion->getTestFile()
                     .  '('
                     .  $assertion->getTestLine()
                     .  '): '
                     .  $assertion->getMessage()
                     .  PHP_EOL;
            }
        } else {
            $str .= 'All tests passed!';
        }
        return trim($str);
    }
    
    /**
     * Renders the exceptions in the test.
     * 
     * @param TestInterface $test The test to output.
     * 
     * @return string
     */
    public function renderExceptions(ReporterInterface $test)
    {
        $str = '';
        if ($exceptions = $test->getExceptions()) {
            $str .= 'Errors' . PHP_EOL;
            $str .= '------' . PHP_EOL;
            foreach ($exceptions as $exception) {
                $str .= $exception->getCode() . '. ' . $exception->getFile() . '(' . $exception->getLine() . ')';
                $str .= ': ' . $exception->getMessage();
                $str .= PHP_EOL;
                foreach (explode(PHP_EOL, $exception->getTraceAsString()) as $line) {
                    $str .= '    ' . $line . PHP_EOL;
                }
                $str .= PHP_EOL;
            }
        }
        return trim($str);
    }

    /**
     * Renders the time.
     * 
     * @return string
     */
    public function renderTime(ReporterInterface $test)
    {
        $str  = 'Tests completed in ' . $test->getTime() . ' seconds';
        $str .= ' using ' . $test->getMemory() . ' bytes of memory.';
        return $str;
    }
}
