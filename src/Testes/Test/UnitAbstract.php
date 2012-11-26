<?php

namespace Testes\Test;
use ArrayIterator;
use Exception;
use LogicException;
use ReflectionClass;
use Testes\Assertion\Assertion;
use Testes\Assertion\Set;
use Testes\Fixture\FixtureInterface;
use Testes\RunableAbstract;
use Testes\RunableInterface;

/**
 * Abstract test class that implements all methods for test suites and base class.
 * 
 * @category UnitTesting
 * @package  Testes
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
abstract class UnitAbstract extends RunableAbstract implements TestInterface
{
    /**
     * The test method names.
     * 
     * @var array
     */
    private $methods;
    
    /**
     * The assertions made on a test.
     * 
     * @var array
     */
    private $assertions = array();
    
    /**
     * Any exceptions thrown during the running of the tests.
     * 
     * @var array
     */
    private $exceptions = array();
    
    /**
     * Constructs a new abstract unit test.
     * 
     * @return UnitAbstract
     */
    public function __construct()
    {
        $this->methods    = $this->getMethods();
        $this->assertions = new Set;
        $this->exceptions = new ArrayIterator;
    }

    /**
     * Adds a fixture to the unit test.
     * 
     * @param string           $name    The fixture name.
     * @param FixtureInterface $fixture The fixture to add.
     * 
     * @return UnitAbstract
     */
    public function setFixture($name, FixtureInterface $fixture)
    {
        $this->fixtures[$name] = $fixture;
        return $this;
    }

    /**
     * Returns the specified fixture.
     * 
     * @param string $name The fixture name.
     * 
     * @throws LogicException If the fixture does not exist.
     * 
     * @return FixtureInterface
     */
    public function getFixture($name)
    {
        if (isset($this->fixtures[$name])) {
            return $this->fixtures[$name];
        }

        throw new LogicException(sprintf('The fixture "%s" does not exist.', $name));
    }
    
    /**
     * Runs all test methods.
     * 
     * @return UnitAbstract
     */
    public function run()
    {
        $this->setUp();

        foreach ($this->fixtures as $fixture) {
            $fixture->setUp();
        }

        foreach ($this->methods as $method) {
            try {
                $this->$method();
            } catch (Exception $e) {
                $this->exceptions[] = $e;
            }
        }

        foreach ($this->fixtures as $fixture) {
            $fixture->tearDown();
        }

        $this->tearDown();

        return $this;
    }
    
    /**
     * Creates an assertion.
     * 
     * @param bool   $expression  The expression to test.
     * @param string $description The description of the assertion.
     * @param int    $code        A code if necessary.
     * 
     * @return UnitAbstract
     */
    public function assert($expression, $description = null, $code = Assertion::DEFAULT_CODE)
    {
        $this->assertions->add(new Assertion($expression, $description, $code));
        return $this;
    }
    
    /**
     * Returns all assertions made in the test.
     * 
     * @return Set
     */
    public function getAssertions()
    {
        return $this->assertions;
    }
    
    /**
     * Returns the exceptions thrown during the tests.
     * 
     * @return ArrayIterator
     */
    public function getExceptions()
    {
        return $this->exceptions;
    }
    
    /**
     * Returns the number of test methods in the test.
     * 
     * @return int
     */
    public function count()
    {
        return count($this->methods);
    }
    
    /**
     * Returns all public methods that are valid test methods.
     * 
     * @return array
     */
    private function getMethods()
    {
        $exclude = array();
        $include = array();
        $self    = new ReflectionClass($this);
        
        // exclude any methods from the interfaces
        foreach ($self->getInterfaces() as $interface) {
            foreach ($interface->getMethods() as $method) {
                $exclude[] = $method->getName();
            }
        }
        
        // exclude any methods from the traits
        foreach ($self->getTraits() as $trait) {
            foreach ($trait->getMethods() as $method) {
                $exclude[] = $method->getName();
            }
        }
        
        // exclude methods
        foreach ($self->getMethods() as $method) {
            if (!$method->isPublic()) {
                continue;
            }
            
            // make sure it was delcared by the test class
            if ($method->getDeclaringClass()->getName() !== get_class($this)) {
                continue;
            }
    
            // exclude particular methods
            $method = $method->getName();
            if (in_array($method, $exclude)) {
                continue;
            }
            $include[] = $method;
        }
        
        // make sure no duplicates are returned
        return array_unique($include);
    }
}