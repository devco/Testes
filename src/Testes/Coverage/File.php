<?php

namespace Testes\Coverage;
use ArrayIterator;
use Countable;
use IteratorAggregate;

/**
 * Represents a single file.
 * 
 * @category UnitTesting
 * @package  Testes
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
class File implements Countable, IteratorAggregate
{
    /**
     * The file being analyzed.
     * 
     * @var string
     */
    private $file;
    
    /**
     * The file lines in the file.
     * 
     * @var ArrayIterator
     */
    private $lines;
    
    /**
     * The testes lines.
     * 
     * @var ArrayIterator
     */
    private $tested;
    
    /**
     * The untested lines
     * 
     * @var ArrayIterator
     */
    private $untested;
    
    /**
     * The dead lines
     * 
     * @var ArrayIterator
     */
    private $dead;
    
    /**
     * The ignored lines
     * 
     * @var ArrayIterator
     */
    private $ignored;
    
    /**
     * The untested lines
     * 
     * @var ArrayIterator
     */
    private $result;
    
    /**
     * Sets up a new file to analyze.
     * 
     * @param string         $file   The full path to the file.
     * @param CoverageResult $result The coverage result.
     * 
     * @return File
     */
    public function __construct($file, CoverageResult $result)
    {
        $real = realpath($file);
        
        if (!$real) {
            throw new InvalidArgumentException('The file "' . $file . '" does not exist.');
        }
        
        $this->file     = $real;
        $this->lines    = new ArrayIterator;
        $this->tested   = new ArrayIterator;
        $this->untested = new ArrayIterator;
        $this->dead     = new ArrayIterator;
        $this->ignored  = new ArrayIterator;
        $this->result   = $result;
        
        $this->buildLines();
    }
    
    /**
     * Returns the file path.
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->file;
    }
    
    /**
     * Returns each line in the file as an iterator.
     * 
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return $this->lines;
    }
    
    /**
     * Returns the number of lines in the file.
     * 
     * @return int
     */
    public function count()
    {
        return $this->lines->count();
    }
    
    /**
     * Returns whether or not the file is fully tested.
     * 
     * @return bool
     */
    public function isTested()
    {
        return $this->untested->count() === 0;
    }
    
    /**
     * Returns whether or not the file has untested lines.
     * 
     * @return bool
     */
    public function isUntested()
    {
        return $this->untested->count() > 0;
    }
    
    /**
     * Returns whether or not the file is executable.
     * 
     * @return bool
     */
    public function isDead()
    {
        return $this->dead->count() === $this->line->count();
    }
    
    /**
     * Returns whether or not the file is ignored.
     * 
     * @return bool
     */
    public function isIgnored()
    {
        return $this->ignored->count() === $this->line->count();
    }
    
    /**
     * Returns the tested lines.
     * 
     * @return ArrayIterator
     */
    public function getTestedLines()
    {
        return $this->tested;
    }
    
    /**
     * Returns the untested lines.
     * 
     * @return ArrayIterator
     */
    public function getUntestedLines()
    {
        return $this->untested;
    }
    
    /**
     * Returns the dead lines.
     * 
     * @return ArrayIterator
     */
    public function getDeadLines()
    {
        return $this->dead;
    }
    
    /**
     * Returns the ignored lines.
     * 
     * @return ArrayIterator
     */
    public function getIgnoredLines()
    {
        return $this->ignored;
    }
    
    /**
     * Returns the number of tested lines.
     * 
     * @return int
     */
    public function getTestedLineCount()
    {
        return $this->tested->count();
    }
    
    /**
     * Returns the number of untested lines.
     * 
     * @return int
     */
    public function getUntestedLineCount()
    {
        return $this->untested->count();
    }
    
    /**
     * Returns the number of dead lines.
     * 
     * @return int
     */
    public function getDeadLineCount()
    {
        return $this->dead->count();
    }
    
    /**
     * Returns the number of ignored lines.
     * 
     * @return int
     */
    public function getIgnoredLineCount()
    {
        return $this->ignored->count();
    }
    
    /**
     * Returns what percentage of the file is tested.
     * 
     * @return int
     */
    public function getPercentTested()
    {
        $tested   = $this->getTestedLineCount();
        $untested = $this->getUntestedLineCount();
        $total    = $tested + $untested;
        
        if (!$untested) {
            return 100;
        }
        
        if (!$tested) {
            return 0;
        }
        
        return $tested / $total * 100;
    }
    
    /**
     * Analyzes each line.
     * 
     * @return void
     */
    private function buildLines()
    {
        foreach (file($this->file) as $num => $line) {
            ++$num;
            
            $status = $this->result->line($this->file, $num);
            $line   = new Line($line, $num, $status);
            
            // add to all lines
            $this->lines->offsetSet($num, $line);
            
            // detect type of line
            if ($line->isTested()) {
                $this->tested->offsetSet(null, $line);
            } elseif ($line->isUntested()) {
                $this->untested->offsetSet(null, $line);
            } elseif ($line->isDead()) {
                $this->dead->offsetSet(null, $line);
            } elseif ($line->isIgnored()) {
                $this->ignored->offsetSet(null, $line);
            }
        }
    }
}