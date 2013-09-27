<?php

namespace Testes\Finder;
use DirectoryIterator;
use ReflectionClass;
use Testes\Suite\Suite;
use Testes\Event;

/**
 * Default finder implementation.
 * 
 * @category UnitTesting
 * @package  Testes
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
class Finder implements FinderInterface
{
    const SUFFIX = '.php';

    const SUITE = 'Testes\Suite\SuiteInterface';

    const TEST = 'Testes\Test\TestInterface';

    private $runableChecks = [];

    private $suite;

    private $path;

    private $namespace;

    private $pathWithNamespace;

    private $realpathWithNamespace;

    /**
     * Constructs a new finder instance and sets up the test suites.
     * 
     * @param string $dir The base directory to look in.
     * 
     * @return Finder
     */
    public function __construct($path, $namespace = null)
    {
        $this->suite     = new Suite;
        $this->path      = $path;
        $this->namespace = trim($namespace, '\\');
        
        $this->pathWithNamespace = rtrim($path, DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR 
            . str_replace('\\', DIRECTORY_SEPARATOR, $this->namespace);
        
        $this->realpathWithNamespace = realpath($this->pathWithNamespace);
        
        if (!$this->realpathWithNamespace) {
            if ($this->isTestFile($this->pathWithNamespace)) {
                $this->addFile($this->pathWithNamespace);
            }
        } else {
            $this->addDirectory($this->pathWithNamespace);
        }
    }

    /**
     * Creates a suite, runs the tests and returns the suite that was run.
     * 
     * @return Suite
     */
    public function getIterator()
    {
        return $this->suite;
    }
    
    /**
     * Runs the suite and returns it.
     * 
     * @return Suite
     */
    public function run(Event\Test $event = null)
    {
        return $this->suite->run($event);
    }
    
    /**
     * Adds a file to the suite.
     * 
     * @param string $path The test path.
     * 
     * @return void
     */
    private function addFile($path)
    {
        if ($this->isTestFile($path)) {
            $class = $this->resolveClassNameFromPath($path);
            $this->suite->addTest(new $class);
        }
    }
    
    /**
     * Adds a directory to the suite.
     * 
     * @param string $path The test path.
     * 
     * @return void
     */
    private function addDirectory($path)
    {
        foreach (new DirectoryIterator($path) as $item) {
            if ($item->isDot()) {
                continue;
            }
            
            $class = $this->resolveClassNameFromPath($item->getRealpath());
            
            if ($item->isDir()) {
                if ($this->isTestFile($item->getRealpath()) && $this->isSuite($class)) {
                    $this->suite = new $class;
                }
                $this->suite->addTests(new static($this->path, $class));
            } elseif ($this->isTest($class)) {
                $this->suite->addTest(new $class);
            }
        }
    }
    
    /**
     * Formats a class name from the path and returns it.
     * 
     * @param string $path The test path.
     * 
     * @return string
     */
    private function resolveClassNameFromPath($path)
    {
        $class = realpath($path);
        $class = preg_replace('/\.php$/', '', $class);
        $class = substr($class, strlen($this->realpathWithNamespace));
        $class = str_replace(DIRECTORY_SEPARATOR, '\\', $class);
        $class = trim($class, '\\');
        $class = $this->namespace . '\\' . $class;
        $class = trim($class, '\\');
        return $class;
    }
    
    /**
     * Returns whether or not the class is a test suite.
     * 
     * @param string $class The test class.
     * 
     * @return bool
     */
    private function isSuite($class)
    {
        return $this->isRunable($class)
            && (new ReflectionClass($class))->implementsInterface(self::SUITE);
    }
    
    /**
     * Returns whether or not the class is a test.
     * 
     * @param string $class The test class.
     * 
     * @return bool
     */
    private function isTest($class)
    {
        return $this->isRunable($class)
            && (new ReflectionClass($class))->implementsInterface(self::TEST);
    }
    
    /**
     * Returns whether or not the class exists.
     * 
     * @param string $class The test class.
     * 
     * @return bool
     */
    private function isRunable($class)
    {
        if (array_key_exists($class, $this->runableChecks)) {
            return $this->runableChecks[$class];
        }

        return $this->runableChecks[$class] = class_exists($class, true)
            && (new ReflectionClass($class))->isInstantiable();
    }
    
    /**
     * Returns whether or not the path has a corresponding file.
     * 
     * @param string $class The test path.
     * 
     * @return bool
     */
    private function isTestFile($path)
    {
        return is_file($path . self::SUFFIX);
    }
}