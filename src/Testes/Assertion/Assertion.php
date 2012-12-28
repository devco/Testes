<?php

namespace Testes\Assertion;

class Assertion implements AssertionInterface
{
    const DEFAULT_CODE = 0;
    
    public function __construct($expression, $message, $code = self::DEFAULT_CODE)
    {
        $this->trace      = debug_backtrace();
        $this->expression = $expression;
        $this->message    = $message;
        $this->code       = $code;
    }
    
    public function isPassed()
    {
        return $this->expression;
    }

    public function isFailed()
    {
        return !$this->expression;
    }
    
    public function getMessage()
    {
        return $this->message;
    }
    
    public function getCode()
    {
        return $this->code;
    }
    
    public function getTestFile()
    {
        return $this->trace[1]['file'];
    }
    
    public function getTestLine()
    {
        return $this->trace[1]['line'];
    }
    
    public function getTestClass()
    {
        return $this->trace[2]['class'];
    }
    
    public function getTestMethod()
    {
        return $this->trace[2]['function'];
    }
}