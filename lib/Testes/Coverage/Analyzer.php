<?php

namespace Testes\Coverage;
use Arrayiterator;
use Closure;
use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use SplFileObject;

class Analyzer implements Countable, IteratorAggregate
{
	private $result;

	private $files = array();

	public function __construct(CoverageResult $result)
	{
    	$this->files  = new ArrayIterator;
		$this->result = $result;
	}
	
	public function count()
	{
    	return $this->files->count();
	}
	
	public function getIterator()
	{
	    return $this->files;
	}

	public function addFile($file)
	{
	    $this->files[] = new File($file, $this->result);
		return $this;
	}

	public function addDirectory($dir)
	{
		foreach ($this->getRecursiveIterator($dir) as $item) {
			if ($item->isFile()) {
				$this->addFile($item);
			}
		}
		return $this;
	}
	
	public function is($pattern, $mods = null)
	{
    	return $this->filter(function($file) use ($pattern, $mods) {
    	    return preg_match('#' . $pattern . '#' . $mods, $file->__toString());
    	});
	}
	
	public function not($pattern, $mods = null)
	{
    	return $this->filter(function($file) use ($pattern, $mods) {
            return !preg_match('#' . $pattern . '#' . $mods, $file->__toString());
    	});
	}
	
	public function filter(Closure $filter)
	{
	    foreach ($this->files as $index => $file) {
	        if (!$filter($file)) {
	            unset($this->files[$index]);
	        }
	    }
	    return $this;
	}
	
	public function getTestedFiles()
	{
    	$files = new ArrayIterator;
    	foreach ($this->files as $file) {
    	    if ($file->tested()) {
    	        $files[] = $file;
    	    }
    	}
    	return $files;
	}
	
	public function getTestedFileCount()
	{
    	return $this->getTestedFiles()->count();
	}
	
	public function getUntestedFiles()
	{
    	$files = new ArrayIterator;
    	foreach ($this->files as $file) {
        	if (!$file->tested()) {
            	$files[] = $file;
        	}
    	}
    	return $files;
	}
	
	public function getUntestedFileCount()
	{
    	return $this->getUntestedFiles()->count();
	}
	
	public function getDeadFiles()
	{
    	$files = new ArrayIterator;
    	foreach ($this->files as $file) {
        	if ($file->getDeadLineCount()) {
            	$files[] = $file;
        	}
    	}
    	return $files;
	}
	
	public function getDeadFileCount()
	{
    	return $this->getDeadFiles()->count();
	}
	
	public function getLineCount()
	{
    	return $this->getSumOf('count');
    }
    
    public function getExecutedLineCount()
    {
        return $this->getSumOf('getExecutedLineCount');
    }
    
    public function getUnexecutedLineCount()
    {
        return $this->getSumOf('getUnexecutedLineCount');
    }
    
    public function getDeadLineCount()
    {
        return $this->getSumOf('getDeadLineCount');
    }

	public function getPercentTested()
	{
	    $sum = $this->getSumOf('getPercentTested');
	    $all = $this->count() * 100;
	    return $sum / $all * 100;
	}

	/**
     * Returns the recursive iterator.
     * 
     * @param string $dir The directory to get the recursive iterator for.
     * 
     * @return RecursiveIteratorIterator
     */
    private function getRecursiveIterator($dir)
    {
        return new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir),
            RecursiveIteratorIterator::SELF_FIRST
        );
    }
    
    /**
     * Returns the sum of all return values of the specified method on all files.
     * 
     * @param string $method The method to call
     * 
     * @return int
     */
    private function getSumOf($method)
    {
        $sum = 0;
        foreach ($this->files as $file) {
            $sum += $file->$method();
        }
        return $sum;
    }
}
