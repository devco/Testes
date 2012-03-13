<?php

namespace Testes\Coverage;
use ArrayIterator;
use Countable;
use IteratorAggregate;

class File implements Countable, IteratorAggregate
{
    private $file;
    
    private $split;
    
    private $coverage;
    
    public function __construct($file, CoverageResult $coverage)
    {
        $real = realpath($file);
        
        if (!$real) {
            throw new InvalidArgumentException('The file "' . $file . '" does not exist.');
        }
        
        $this->file     = $real;
        $this->split    = new ArrayIterator(file($this->file));
        $this->coverage = $coverage;
    }
    
    public function __toString()
    {
        return $this->file;
    }
    
    public function getIterator()
    {
        return new $this->split;
    }
    
    public function count()
    {
        return count(file($this->file));
    }
    
    public function tested()
    {
        return $this->getUnexecutedLineCount() === 0;
    }
    
    public function getExecutedLines()
    {
        return $this->coverage->getExecutedLines($this->file);
    }
    
    public function getExecutedLineCount()
    {
        return count($this->getExecutedLines());
    }
    
    public function getUnexecutedLines()
    {
        return $this->coverage->getUnexecutedLines($this->file);
    }
    
    public function getUnexecutedLineCount()
    {
        return count($this->getUnexecutedLines());
    }
    
    public function getDeadLines()
    {
        return $this->coverage->getDeadLines($this->file);
    }
    
    public function getDeadLineCount()
    {
        return count($this->getDeadLines());
    }
    
    public function getPercentTested()
    {
        $tested   = $this->getExecutedLineCount();
        $untested = $this->getUnexecutedLineCount();
        $total    = $tested + $untested;
        
        if (!$untested) {
            return 100;
        }
        
        if (!$tested) {
            return 0;
        }
        
        return $tested / $total * 100;
    }
}