<?php

namespace Testes\Output;
use Testes\AssertionInterface;
use Testes\OutputInterface;
use Testes\TestInterface;

/**
 * Renders the test output in JUnit format.
 * 
 * @category UnitTesting
 * @package  Testes
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
class Junit implements OutputInterface
{
    /**
     * Renders the test results.
     * 
     * @param TestInterface $test The test to output.
     * 
     * @return string
     */
    public function render(TestInterface $test)
    {
        $str  = '<testsuites>';
        $str .= PHP_EOL;
        $str .= $this->renderTestSuites($test);
        $str .= PHP_EOL;
        $str .= '</testsuite>';
        $str .= PHP_EOL;
        return $str;
    }
    
    /** 
     * Renders the test suites' output.
     * 
     * @param TestInterface $test The test to output.
     * 
     * @return string
     */
    private function renderTestSuites(TestInterface $test)
    {
        $str = $this->renderTestSuite($test);
        return $str;
    }
    
    /**
     * Renders the test suite output.
     * 
     * @param TestInterface $test The test to output.
     * 
     * @return string
     */
    private function renderTestSuite(TestInterface $test)
    {
        $str  = '    <testsuite errors="' . count($test->getExceptions()) . '" failures="' . count($test->getFailedAssertions()) . '">';
        $str .= PHP_EOL;
        if ($rendered = $this->renderProperties($test)) {
            $str .= $rendered;
            $str .= PHP_EOL;
        }
        $str .= $this->renderTestCases($test->getTests());
        if ($rendered = $this->renderSystemOut($test)) {
            $str .= $rendered;
            $str .= PHP_EOL;
        }
        if ($rendered = $this->renderSystemErr($test)) {
            $str .= $rendered;
            $str .= PHP_EOL;
        }
        $str .= '    </testsuite>';
        return $str;
    }
    
    /**
     * Renders the test suite property.
     * 
     * @param TestInterface $test The test to output.
     * 
     * @return string
     */
    private function renderProperties(TestInterface $test)
    {
        
    }
    
    /**
     * Renders a single test suite property.
     * 
     * @param string $property The property to render.
     * 
     * @return string
     */
    private function renderProperty($property)
    {
        
    }
    
    /**
     * Renders a single test suite's cases.
     * 
     * @param array $tests The tests to render.
     * 
     * @return string
     */
    private function renderTestCases(array $tests)
    {
        $str  = '        <testcases>';
        $str .= PHP_EOL;
        foreach ($tests as $test) {
            $str .= '            ' . $this->renderTestCase($test);
            $str .= PHP_EOL;
        }
        $str .= '        </testcases>';
        $str .= PHP_EOL;
        return $str;
    }
    
    /**
     * Renders a single test case from the suite's cases.
     * 
     * @param TestInterface $test The test to output.
     * 
     * @return string
     */
    private function renderTestCase(TestInterface $test)
    {
        $str  = '<testcase';
        $str .= ' classname="' . get_class($test) . '"';
        $str .= ' name="' . get_class($test) . '"';
        $str .= ' time="' . $test->getTime() . '"';
        $str .= ' memory="' . $test->getMemory() . '"';
        $str .= ' />';
        return $str;
    }
    
    /**
     * Renders the system standard output.
     * 
     * @param TestInterface $test The test to output.
     * 
     * @return string
     */
    private function renderSystemOut(TestInterface $test)
    {
        $cli = new Cli;
        $str = '        <system-out><![CDATA[';
        if ($rendered = $cli->renderAssertions($test)) {
            $str .= $rendered;
        }
        $str .= ']]></system-out>';
        return $str;
    }
    
    /**
     * Renders the system error output.
     * 
     * @param TestInterface $test The test to output.
     * 
     * @return string
     */
    private function renderSystemErr(TestInterface $test)
    {
        $cli = new Cli;
        $str = '        <system-err><![CDATA[';
        if ($rendered = $cli->renderExceptions($test)) {
            $str .= $rendered;
        }
        $str .= ']]></system-err>';
        return $str;
    }
}