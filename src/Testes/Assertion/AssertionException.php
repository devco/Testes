<?php

namespace Testes\Assertion;
use Exception;

class AssertionException implements AssertionInterface
{
    private $exception;

    private $fileLineTrace;

    private $classMethodTrace;

    public function __construct(Exception $e)
    {
        $this->exception = $e;

        foreach ($e->getTrace() as $index => $trace) {
            if (isset($trace['class']) && is_subclass_of($trace['class'], 'Testes\Test\TestInterface')) {
                $this->fileLineTrace    = $trace;
                $this->classMethodTrace = $e->getTrace()[$index + 1];
                break;
            }
        }
    }

    public function isPassed()
    {
        return false;
    }

    public function isFailed()
    {
        return true;
    }

    public function getMessage()
    {
        return $this->exception->getMessage();
    }

    public function getCode()
    {
        return $this->exception->getCode();
    }

    public function getException()
    {
        return $this->exception;
    }

    public function getTestFile()
    {
        return $this->fileLineTrace['file'];
    }

    public function getTestLine()
    {
        return $this->fileLineTrace['line'];
    }

    public function getTestClass()
    {
        return $this->classMethodTrace['class'];
    }

    public function getTestMethod()
    {
        return $this->classMethodTrace['function'];
    }
}