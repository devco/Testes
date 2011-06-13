<?php

namespace Testes;

/**
 * Base test class. Subclasses need only implement test methods.
 * 
 * @category UnitTesting
 * @package  Testes
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
abstract class Test extends TestAbstract
{
    /**
     * Runs all test methods.
     * 
     * @return Test
     */
    public function run()
    {
        $this->setUp();
        $this->startMemoryCounter();
        $this->startTimer();
        foreach ($this->getMethods() as $test) {
            $this->$test();
        }
        $this->stopTimer();
        $this->stopMemoryCounter();
        $this->tearDown();
        return $this;
    }
    
    /**
     * Returns all public methods that are valid test methods.
     * 
     * @return array
     */
    public function getMethods()
    {
        $exclude = array();
        $include = array();
        $self    = new \ReflectionClass($this);
        foreach ($self->getInterfaces() as $interface) {
            foreach ($interface->getMethods() as $method) {
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

            $method = $method->getName();
            if (in_array($method, $exclude)) {
                continue;
            }
            $include[] = $method;
        }
        return array_unique($include);
    }
}