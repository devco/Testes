<?php

namespace Testes\Assertion;

interface AssertionInterface
{
    public function isFailed();

    public function isPassed();
    
    public function getMessage();
    
    public function getCode();
    
    public function getTestFile();
    
    public function getTestLine();
    
    public function getTestClass();
    
    public function getTestMethod();
}