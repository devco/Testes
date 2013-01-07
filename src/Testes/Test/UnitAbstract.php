<?php

namespace Testes\Test;
use ArrayIterator;
use Exception;
use LogicException;
use ReflectionClass;
use ReflectionMethod;
use RuntimeException;
use Testes\Assertion\Assertion;
use Testes\Assertion\AssertionArray;
use Testes\Benchmark\Benchmark;
use Testes\Benchmark\BenchmarkArray;
use Testes\Fixture\FixtureInterface;
use Testes\Fixture\Manager;
use Testes\RunableAbstract;
use Testes\RunableInterface;

abstract class UnitAbstract extends RunableAbstract implements TestInterface
{
    private $methods;

    private $assertions;

    private $benchmarks;

    private $exceptions;

    private $fixtureManager;

    private $fixtures = [];

    public function __construct()
    {
        $this->methods        = $this->getMethods();
        $this->assertions     = new AssertionArray;
        $this->benchmarks     = new BenchmarkArray;
        $this->exceptions     = new ArrayIterator;
        $this->fixtureManager = new Manager;
    }

    public function __set($name, FixtureInterface $fixture)
    {
        $this->fixtures[$name] = $fixture;
        return $this;
    }

    public function __get($name)
    {
        if (isset($this->fixtures[$name])) {
            return $this->fixtures[$name];
        }

        throw new LogicException(sprintf('The fixture "%s" does not exist for test "%s".', $name, get_class()));
    }

    public function run(callable $after = null)
    {
        $this->setUp();
        $this->fixtureManager->initAll($this->fixtures);
        $this->fixtureManager->installAll($this->fixtures);

        foreach ($this->methods as $method) {
            set_error_handler($this->generateErrorHandler($method));

            if ($this->benchmarks->has($method)) {
                $this->benchmarks->get($method)->start();
            }

            try {
                $this->$method();
            } catch (Exception $e) {
                $this->exceptions[] = $e;
            }

            if ($this->benchmarks->has($method)) {
                $this->benchmarks->get($method)->stop();
            }

            restore_error_handler();
        }

        $this->fixtureManager->uninstallAll();
        $this->tearDown();

        if ($after) {
            $after($this);
        }

        return $this;
    }

    public function assert($expression, $description = null, $code = Assertion::DEFAULT_CODE)
    {
        $this->assertions->add(new Assertion($expression, $description, $code));
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