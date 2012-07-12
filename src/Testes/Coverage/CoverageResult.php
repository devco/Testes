<?php

namespace Testes\Coverage;
use InvalidArgumentException;

/**
 * Represents the result from XDebug.
 * 
 * @category UnitTesting
 * @package  Testes
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
class CoverageResult
{
    /**
     * The XDebug result.
     * 
     * @var array
     */
    private $result;

    /**
     * Sets up a new result.
     * 
     * @param array $result The coverage result.
     * 
     * @return CoverageResult
     */
    public function __construct(array $result)
    {
        $this->result = $result;
    }
    
    /**
     * Gets the specified file information.
     * 
     * @param string $file The file to get information about.
     * 
     * @return array
     */
    public function file($file)
    {
        if (isset($this->result[$file])) {
            return $this->result[$file];
        }
        return array();
    }
    
    /**
     * Gets the specified line information.
     * 
     * @param string $line The line to get information about.
     * 
     * @return array
     */
    public function line($file, $number)
    {
        $lines = $this->file($file);
        if (isset($lines[$number])) {
            return $lines[$number];
        }
        return 0;
    }
}