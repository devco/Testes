<?php

namespace Testes\Finder;
use DirectoryIterator;
use ReflectionClass;
use Testes\Suite\Suite;
use UnexpectedValueException;

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
    /**
     * The test file suffix.
     * 
     * @var string
     */
    const SUFFIX = '.php';
    
    /**
     * The runable interface.
     * 
     * @var string
     */
    const SUITE = 'Testes\Suite\SuiteInterface';
    
    /**
     * The runable interface.
     * 
     * @var string
     */
    const TEST = 'Testes\Test\TestInterface';
    
    /**
     * The array of suites that were found.
     * 
     * @var string
     */
    private $suite;
    
    /**
     * The original path.
     * 
     * @var string
     */
    private $path;
    
    /**
     * The namespace to use.
     * 
     * @var string
     */
    private $namespace;
    
    /**
     * The fullpath to the finder dir.
     * 
     * @var string
     */
    private $pathWithNamespace;
    
    /**
     * The full path to the tests.
     * 
     * @var string
     */
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
        
        if ($this->realpathWithNamespace) {
            $this->addDirectory($this->pathWithNamespace);
        } else {
            $this->addFile($this->pathWithNamespace);
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
    public function run()
    {
        $this->suite->run();
        return $this->suite;
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
        $class = $this->resolveClassNameFromPath($path);
        
        if (is_file($path) && $this->isTest($class)) {
            $this->suite->addTest(new $class);
        } else {
            throw new UnexpectedValueException(sprintf(
                'The path "%s" is not a valid test.',
                $path
            ));
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
     * Returns whether or not the class is a test.
     * 
     * @param string $class The test class.
     * 
     * @return bool
     */
    private function isTest($class)
    {
        if (!class_exists($class, true)) {
            return false;
        }

        $class = new ReflectionClass($class);
        
        return $class->isInstantiable() && $class->implementsInterface(self::TEST);
    }
}