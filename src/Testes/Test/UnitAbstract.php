<?php

namespace Testes\Test;

use Exception;
use ArrayIterator;
use ReflectionClass;
use RuntimeException;
use Testes\Assertion\Assertion;
use Testes\Assertion\AssertionArray;
use Testes\Benchmark\Benchmark;
use Testes\Benchmark\BenchmarkArray;
use Testes\Fixture\FixtureInterface;
use Testes\Fixture\Manager;
use Testes\RunableAbstract;
use Testes\Event;

abstract class UnitAbstract extends RunableAbstract implements TestInterface
{
    private $methods;

    private $assertions;

    private $benchmarks;

    private $exceptions;

    private $methodExceptions;

    private $methodAssertions;

    private $currentMethod;

    private $fixtures;

    public function __construct()
    {
        $this->methods    = $this->getMethods();
        $this->assertions = new AssertionArray;
        $this->benchmarks = new BenchmarkArray;
        $this->exceptions = new ArrayIterator;
        $this->fixtures   = new Manager;
    }

    public function __set($name, FixtureInterface $fixture)
    {
        $this->fixtures->set($name, $fixture);
    }

    public function __get($name)
    {
        return $this->fixtures->get($name);
    }

    public function __isset($name)
    {
        $this->fixtures->has($name);
    }

    public function __unset($name)
    {
        $this->fixtures->remove($name);
    }

    public function run(Event\Test $event = null)
    {
        if ($event) {
            $event->preRun($this);
        }

        $this->setUp();
        $this->fixtures->install();

        foreach ($this->methods as $method) {
            $this->currentMethod = $method;
            $this->methodExceptions[$method] = new ArrayIterator;
            $this->methodAssertions[$method] = new AssertionArray;

            if ($event) {
                $event->preMethod($method, $this);
            }

            set_error_handler($this->generateErrorHandler($method));

            if ($this->benchmarks->has($method)) {
                $this->benchmarks->get($method)->start();
            }

            try {
                $this->$method();
            } catch (Exception $e) {
                $assertionException = new AssertionException($e);
                $this->exceptions->append($assertionException);
                $this->methodExceptions[$method]->append($assertionException);
            }

            if ($this->benchmarks->has($method)) {
                $this->benchmarks->get($method)->stop();
            }

            restore_error_handler();

            if ($event) {
                $event->postMethod($method, $this);
            }
        }

        $this->tearDown();
        $this->fixtures->uninstall();

        if ($event) {
            $event->postRun($this);
        }

        return $this;
    }

    public function assert($expression, $description = null, $code = Assertion::DEFAULT_CODE)
    {
        $assertion = new Assertion($expression, $description, $code);
        $this->assertions->add($assertion);
        $this->methodAssertions[$this->currentMethod]->add($assertion);

        return $this;
    }

    public function benchmark($method)
    {
        $this->benchmarks->add($method, new Benchmark);
        return $this;
    }

    public function isPassed()
    {
        return $this->getAssertions()->isPassed() && !$this->getExceptions()->count();
    }

    public function isMethodPassed($method)
    {
        return
            $this->methodAssertions[$method]->isPassed() ||
            !isset($this->methodExceptions[$method]);
    }

    public function isFailed()
    {
        return !$this->isPassed();
    }

    public function getAssertions()
    {
        return $this->assertions;
    }

    public function getExceptions()
    {
        return $this->exceptions;
    }

    public function getBenchmarks()
    {
        return $this->benchmarks;
    }

    public function count()
    {
        return count($this->methods);
    }

    private function getMethods()
    {
        $exclude = array();
        $include = array();
        $self    = new ReflectionClass($this);

        foreach ($self->getInterfaces() as $interface) {
            foreach ($interface->getMethods() as $method) {
                $exclude[] = $method->getName();
            }
        }

        foreach ($self->getTraits() as $trait) {
            foreach ($trait->getMethods() as $method) {
                $exclude[] = $method->getName();
            }
        }

        foreach ($self->getMethods() as $method) {
            if (!$method->isPublic()) {
                continue;
            }

            if ($method->getDeclaringClass()->getName() !== get_class($this)) {
                continue;
            }

            $method = $method->getName();

            if (in_array($method, $exclude)) {
                continue;
            }

            $include[] = $method;
        }

        return array_unique($include);
    }

    private function generateErrorHandler($method)
    {
        $msg = sprintf('Error running test "%s::%s()"', get_class($this), $method);

        return function($errno, $errstr, $errfile, $errline) use ($msg) {
            throw new RuntimeException("$msg: \"$errstr\" in $errfile on line $errline.", $errno);
        };
    }
}