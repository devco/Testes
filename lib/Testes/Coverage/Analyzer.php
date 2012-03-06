<?php

namespace Testes\Coverage;

class Analyzer
{
	private $result;

	private $files = array();

	public function __construct(CoverageResult $result)
	{
		$this->result = $result;
	}

	public function addFile($file)
	{
		// ensure the file exists
		$real = realpath($file);
		if (!$real || !is_file($file)) {
			throw new \InvalidArgumentException("Cannot analyze against non-existent file {$file}.");
		}

		// append the file
		$this->files[] = $real;

		// make sure there are no duplicates
		$this->files = array_unique($this->files);

		return $this;
	}

	public function addDirectory($dir)
	{
		foreach ($this->getRecursiveIterator($dir) as $item) {
			if ($this->isValidFile($item)) {
				$this->addFile($item->getRealpath());
			}
		}
		return $this;
	}

	public function getTestedFiles()
	{
		return array_intersect($this->files, $this->result->getFiles());
	}

	public function getUntestedFiles()
	{
		return array_diff($this->files, $this->result->getFiles());
	}

	public function getPercentage($accuracy = 2, $includeUntested = true)
	{
		$executed   = 0;
		$unexecuted = 0;

		// count the executed and unexecuted lines of each tested file
		foreach ($this->getTestedFiles() as $file) {
			$executed   += count($this->result->getExecutedLines($file));
			$unexecuted += count($this->result->getUnexecutedLines($file));
		}

		// add to the unexecuted lines of each
		if ($includeUntested) {
    		foreach ($this->getUntestedFiles() as $file) {
    			$unexecuted += count(file($file));
    		}
    	}

		$total   = $executed + $unexecuted;
		$percent = $executed / $total;
		$percent = $percent * 100;

		return round($percent, $accuracy);
	}

	/**
     * Returns the recursive iterator.
     * 
     * @param string $dir The directory to get the recursive iterator for.
     * 
     * @return \RecursiveIteratorIterator
     */
    private function getRecursiveIterator($dir)
    {
        return new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir),
            \RecursiveIteratorIterator::SELF_FIRST
        );
    }
    
    /**
     * Returns whether or not the specified file is valid.
     * 
     * @param \SplFileInfo $item The item to check.
     * 
     * @return bool
     */
    private function isValidFile(\SplFileInfo $item)
    {
        return $item->isFile() && !preg_match('/\/\./', $item->getRealpath());
    }
}
