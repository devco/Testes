<?php

namespace Testes;
use InvalidArgumentException;

/**
 * The autoloader.
 * 
 * @category Autoloading
 * @package  Testes
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
class Autoloader
{
    /**
     * List of paths.
     * 
     * @var array
     */
    private static $paths = [];
    
    /**
     * Registers autoloading.
     * 
     * @return void
     */
    public static function register()
    {
        self::addPath(__DIR__ . '/..');
        spl_autoload_register(array(get_class(), 'autoload'));
    }
    
    /**
     * Adds an autoload path.
     * 
     * @return void
     */
    public static function addPath($path)
    {
        if ($real = realpath($path)) {
            self::$paths[] = $real;
            return;
        }
        
        throw new InvalidArgumentException(sprintf('The autoload path "%s" does not exist.', $path));
    }

    /**
     * Autoloads the specified class.
     * 
     * @param string $class The class to autoload.
     * 
     * @return void
     */
    public static function autoload($class)
    {
        $name = str_replace(array('_', '\\'), DIRECTORY_SEPARATOR, $class) . '.php';
        foreach (self::$paths as $path) {
            $pathname = $path . DIRECTORY_SEPARATOR . $name;
            if (is_readable($pathname)) {
                include $pathname;
                return;
            }
        }
    }
}