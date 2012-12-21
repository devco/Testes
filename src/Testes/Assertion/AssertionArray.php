<?php

namespace Testes\Assertion;
use Countable;
use ArrayIterator;
use IteratorAggregate;
use Traversable;

class AssertionArray implements Countable, IteratorAggregate
{
    private $assertions = [];
    
    public function add(AssertionInterface $assertion)
    {
        $this->assertions[] = $assertion;
        return $this;
    }
    
    public function isFailed()
    {
        return $this->getFailed()->count() > 0;
    }
    
    public function isPassed()
    {
        return $this->getFailed()->count() === 0;
    }
    
    public function getFailed()
    {
        $failed = new ArrayIterator;

        foreach ($this as $assertion) {
            if ($assertion->failed()) {
                $failed[] = $assertion;
            }
        }

        return $failed;
    }
    
    public function getPassed()
    {
        $passed = new ArrayIterator;

        foreach ($this as $assertion) {
            if ($assertion->passed()) {
                $passed[] = $assertion;
            }
        }

        return $passed;
    }
    
    public function count()
    {
        return count($this->assertions);
    }
    
    public function getIterator()
    {
        return new ArrayIterator($this->assertions);
    }
}