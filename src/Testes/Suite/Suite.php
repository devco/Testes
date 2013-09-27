<?php

namespace Testes\Suite;
use ArrayIterator;
use IteratorAggregate;
use Testes\Assertion\AssertionArray;
use Testes\Benchmark\BenchmarkArray;
use Testes\RunableAbstract;
use Testes\RunableInterface;
use Testes\Event;
use Traversable;

class Suite extends RunableAbstract implements IteratorAggregate, SuiteInterface
{
    private $tests = array();

    public function getIterator()
    {
        return $this->getTests();
    }

    public function run(Event\Test $event = null)
    {
        $this->setUp();

        foreach ($this->tests as $test) {
            $test->run($event);
        }

        $this->tearDown();

        return $this;
    }

    public function count()
    {
        $count = 0;

        foreach ($this->getTests() as $test) {
            $count += $test->count();
        }

        return $count;
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

    public function getAssertions()
    {
        $assertions = new AssertionArray;

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
                $exceptions->append($exception);
            }
        }

        return $exceptions;
    }

    public function getBenchmarks()
    {
        $benchmarks = new BenchmarkArray;

        foreach ($this->tests as $test) {
            foreach ($test->getBenchmarks() as $name => $benchmark) {
                $benchmarks->add($test->getName() . '::' . $name . '()', $benchmark);
            }
        }

        return $benchmarks;
    }

    public function isPassed()
    {
        return $this->getAssertions()->isPassed() && !$this->getExceptions()->count();
    }

    public function isFailed()
    {
        return !$this->isPassed();
    }
}