<?php

namespace Testes;
use InvalidArgumentException;
use RuntimeException;

class Autoloader
{
    private static $included = [];

    private static $paths = [];

    public static function register()
    {
        self::addPath(__DIR__ . '/..');
        spl_autoload_register(array(get_class(), 'load'));
    }

    public static function addPath($path)
    {
        if ($real = realpath($path)) {
            self::$paths[] = $real;
            return;
        }
        
        throw new InvalidArgumentException(sprintf('The autoload path "%s" does not exist.', $path));
    }

    public static function load($class)
    {
        if (class_exists($class, false)) {
            return;
        }

        $name = str_replace(array('_', '\\'), DIRECTORY_SEPARATOR, $class) . '.php';

        foreach (self::$paths as $path) {
            $pathname = $path . DIRECTORY_SEPARATOR . $name;

            if (in_array($pathname, self::$included)) {
                throw new RuntimeException(sprintf('The file "%s" has already be included', $pathname));
            }

            if (is_readable($pathname)) {
                include $pathname;
                self::$included[] = $pathname;
                return;
            }
        }
    }
}