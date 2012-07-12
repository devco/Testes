<?php

namespace Testes\Coverage;

/**
 * Represents a single line in a file.
 * 
 * @category UnitTesting
 * @package  Testes
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
class Line
{
    /**
     * Status of a line when it is not executed.
     * 
     * @var int
     */
    const UNEXECUTED = -1;
    
    /**
     * Status of a line when it is not able to be executed.
     * 
     * @var int
     */
    const DEAD = -2;
    
    /**
     * Status of a line when it is not testable.
     * 
     * @var int
     */
    const IGNORED = -3;
    
    /**
     * The line as a string.
     * 
     * @var string
     */
    private $line;
    
    /**
     * Trimmed version of the line.
     * 
     * @var string
     */
    private $trimmed;
    
    /**
     * The line number.
     * 
     * @var int
     */
    private $num;
    
    /**
     * The line status.
     * 
     * @var int
     */
    private $status;
    
    /**
     * The number if times the line was executed.
     * 
     * @var int
     */
    private $count;
    
    /**
     * Sets up the line.
     * 
     * @param string $line   The line to represent.
     * @param int    $num    The line number.
     * @param int    $status The line status. Defaults to unexecuted.
     * 
     * @return Line
     */
    public function __construct($line, $num, $status = self::UNEXECUTED)
    {
        $this->line    = (string) $line;
        $this->trimmed = trim($this->line);
        $this->num     = (int) $num;
        $this->status  = (int) $status;
        $this->count   = $this->status > 0 ? $this->status : 0;
        
        // detect the type of line
        $this->analyze();
    }
    
    /**
     * Returns the line.
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->line;
    }
    
    /**
     * Returns the nubmer of times the line was executed.
     * 
     * @return int
     */
    public function count()
    {
        return $this->count;
    }
    
    /**
     * Returns the line number.
     * 
     * @return int
     */
    public function getNumber()
    {
        return $this->num;
    }
    
    /**
     * Returns the line status.
     * 
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }
    
    /**
     * Returns whether or not the line is tests.
     * 
     * @return bool
     */
    public function isTested()
    {
        return $this->status > 0;
    }
    
    /**
     * Returns whether or not the line is not tested.
     * 
     * @return bool
     */
    public function isUntested()
    {
        return $this->status === self::UNEXECUTED || $this->status === 0;
    }
    
    /**
     * Returns whether or not the line is not executable.
     * 
     * @return bool
     */
    public function isDead()
    {
        return $this->status === self::DEAD;
    }
    
    /**
     * Returns whether or not the line is testable.
     * 
     * @return bool
     */
    public function isIgnored()
    {
        return $this->status === self::IGNORED;
    }
    
    /**
     * Analyzes the line.
     * 
     * @return bool
     */
    private function analyze()
    {
        $this->analyzeIgnored();
    }
    
    /**
     * Analyzes if the line is ignorable.
     * 
     * @return void
     */
    private function analyzeIgnored()
    {
        // if the line is empty, ignore it
        if ($this->trimmed === '') {
            $this->status = self::IGNORED;
            return;
        }
        
        // characters that if at the beginning of a line make that line ignorable
        $startMatches = array(
            '<?',
            '?>',
            'namespace',
            'use',
            'abstract',
            'class',
            'interface',
            'trait',
            'const',
            'return',
            'final',
            'static',
            'public',
            'protected',
            'private',
            'function',
            '/',
            '*',
            '\'',
            '"',
            '.',
            '_',
            '[',
            ']'
        );
        
        foreach ($startMatches as $tok) {
            if (strpos($this->trimmed, $tok) === 0) {
                $this->status = self::IGNORED;
                return;
            }
        }
        
        $wholeMatches = array(
            '(',
            ')',
            ');',
            '{',
            '}',
            '[',
            ']'
        );
        
        foreach ($wholeMatches as $tok) {
            if ($this->trimmed === $tok) {
                $this->status = self::IGNORED;
                return;
            }
        }
    }
}