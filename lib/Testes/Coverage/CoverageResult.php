<?php

namespace Testes\Coverage;
use InvalidArgumentException;

class CoverageResult
{
	const EXECUTED = 1;

	const UNEXECUTED = -1;

	const DEAD = -2;

    private $result;

    public function __construct(array $result)
    {
        $this->result = $result;
    }

    public function getExecutedLines($file)
    {
        return $this->filter($file, self::EXECUTED);
    }

    public function getUnexecutedLines($file)
    {
    	return $this->filter($file, self::UNEXECUTED);
    }

    public function getDeadLines($file)
    {
    	return $this->filter($file, self::DEAD);
    }

    private function filter($file, $flag)
    {
        $this->ensureFile($file);
        
    	$lines = array();
    	$files = file($file);
    	
    	foreach ($this->result[$file] as $index => $status) {
    		if ($status === $flag && isset($files[$index])) {
    			$lines[$index] = $files[$index];
    		}
    	}

    	return $lines;
    }
    
    private function ensureFile($file)
    {
        if (isset($this->result[$file])) {
            return;
        }
        
        $this->result[$file] = array();
        foreach (file($file) as $index => $line) {
            $this->result[$file][$index] = $this->determineDeadOrUnexecuted($line);
        }
    }
    
    private function determineDeadOrUnexecuted($line)
    {
        $line = trim($line);
        
        if ($this->isDead($line)) {
            return self::DEAD;
        }
        
        return self::UNEXECUTED;
    }
    
    private function isDead($line)
    {
        return !$line
            || strpos($line, '/') === 0
            || preg_match('/^(public|protected|private|final|function|static)\s/', $line);
    }
}
