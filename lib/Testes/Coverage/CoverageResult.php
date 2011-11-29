<?php

namespace Testes\Coverage;

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

    public function getFiles()
    {
        return array_keys($this->result);
    }

    public function getLines($file)
    {
    	$this->ensureFile($file);
    	return array_keys($this->result[$file]);
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
    	foreach ($this->result[$file] as $line) {
    		if ($line === $flag) {
    			$lines[] = $line;
    		}
    	}

    	return $lines;
    }

    private function ensureFile($file)
    {
    	if (!isset($this->result[$file])) {
    		throw new \InvalidArgumentException('The specified file does not exist.');
    	}
    	return $this;
    }
}
