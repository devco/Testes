<?php

namespace Testes;
use ArrayIterator;
use Traversable;

/**
 * Base class for anything that is runable. Allows for manipulation of general metadata and benchmarking.
 * 
 * @category UnitTesting
 * @package  Testes
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
abstract class RunableAbstract implements RunableInterface
{
    /**
     * The name of the test.
     * 
     * @return string
     */
    private $name;
    
    /**
     * The package this test is in.
     * 
     * @return string
     */
    private $package;
    
    /**
     * Sets up the test.
     * 
     * @return void
     */
    public function setUp()
    {
        
    }
    
    /**
     * Tears down the test.
     * 
     * @return void
     */
    public function tearDown()
    {
        
    }
    
    /**
     * Sets the name of the runable.
     * 
     * @param string $name The name.
     * 
     * @return RunableAbstract
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
    
    /**
     * Returns the name of the runable.
     * 
     * @return string
     */
    public function getName()
    {
        return $this->name ? $this->name : get_class($this);
    }
    
    /**
     * Sets the package of the runable.
     * 
     * @param string $package The package.
     * 
     * @return RunableAbstract
     */
    public function setPackage($package)
    {
        $this->package = $package;
        return $this;
    }
    
    /**
     * Returns the package of the runable.
     * 
     * @return string
     */
    public function getPackage()
    {
        return $this->package ? $this->package : get_class($this);
    }
}