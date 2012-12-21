<?php

namespace Testes\Test;
use ArrayIterator;
use Exception;
use LogicException;
use ReflectionClass;
use Testes\Assertion\Assertion;
use Testes\Assertion\AssertionArray;
use Testes\Benchmark\Benchmark;
use Testes\Benchmark\BenchmarkArray;
use Testes\Fixture\FixtureInterface;
use Testes\RunableAbstract;
use Testes\RunableInterface;

abstract class UnitAbstract extends RunableAbstract implements TestInterface
{
    private $methods;

    private $assertions;

    private $exceptions;

    private $fixtures = [];

    private $benchmarks;

    public function __construct()
    {
        $this->methods    = $this->getMethods();
        $this->assertions = new AssertionArray;
        $this->exceptions = new ArrayIterator;
        $this->benchmarks = new BenchmarkArray;
    }

    public function setFixture($name, FixtureInterface $fixture)
    {
        $this->fixtures[$name] = $fixture;
        return $this;
    }

    public function getFixture($name)
    {
        if (isset($this->fixtures[$name])) {
            return $this->fixtures[$name];
        }

        throw new LogicException(sprintf('The fixture "%s" does not exist.', $name));
    }

    public function run(callable $after = null)
    {
        $this->setUp();

        foreach ($this->fixtures as $fixture) {
            $fixture->setUp();
        }

        foreach ($this->methods as $method) {
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
        }

        foreach (array_reverse($this->fixtures) as $fixture) {
            $fixture->tearDown();
        }

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
}