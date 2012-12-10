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
use UnexpectedValueException;

/**
 * Handles code coverage analysis.
 * 
 * @category UnitTesting
 * @package  Testes
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
class Analyzer implements Countable, IteratorAggregate
{
	/**
	 * The coverage result to analyze.
	 * 
	 * @var CoverageResult
	 */
    private $result;

	/**
	 * The files to analyze.
	 * 
	 * @var ArrayIterator
	 */
	private $files;

	/**
	 * Sets up the analyzer using the coverage result.
	 * 
	 * @param CoverageResult $result The coverage result to analyze.
	 * 
	 * @return Analyzer
	 */
	public function __construct(CoverageResult $result)
	{
    	$this->files  = new ArrayIterator;
		$this->result = $result;
	}
	
	/**
	 * Returns the number of files.
	 * 
	 * @return int
	 */
	public function count()
	{
    	return $this->files->count();
	}
	
	/**
	 * Returns an iterator of all the files.
	 * 
	 * @return ArrayIterator
	 */
	public function getIterator()
	{
	    return $this->files;
	}

	/**
	 * Adds the specified file.
	 * 
	 * @param string $file The file to add.
	 * 
	 * @return Analyzer
	 */
	public function addFile($file)
	{
	    $this->files->offsetSet(null, new File($file, $this->result));
		return $this;
	}
	
	/**
	 * Removes the specified file.
	 * 
	 * @param string $file The file to remove.
	 * 
	 * @return Analyzer
	 */
	public function removeFile($file)
	{
    	if (!is_file($file)) {
        	throw new UnexpectedValueException('Unable to remove the file because "' . $file . '" is not a file.');
    	}
    	
    	$real = realpath($file);
    	foreach ($this->files as $index => $file) {
        	if ($file->__toString() === $real) {
            	$this->files->offsetUnset($index);
            	break;
        	}
    	}
    	
    	return $this;
	}

	/**
	 * Adds all files in the specified directory.
	 * 
	 * @param string $dir The directory to add.
	 * 
	 * @return Analyzer
	 */
	public function addDirectory($dir)
	{
		foreach ($this->getRecursiveIterator($dir) as $item) {
			if ($item->isFile()) {
				$this->addFile($item);
			}
		}
		return $this;
	}
	
	/**
	 * Removes all files in the specified directory.
	 * 
	 * @param string $dir The directory to remove.
	 * 
	 * @return Analyzer
	 */
	public function removeDirectory($dir)
	{
    	if (!is_dir($dir)) {
        	throw new UnexpectedValueException('Unable to remove directory because "' . $dir . '" is not a directory.');
    	}
    	
    	$real = realpath($dir);
    	foreach ($this->files as $index => $file) {
        	if (strpos($file->__toString(), $real) === 0) {
            	$this->files->offsetUnset($index);
        	}
    	}
    	
    	return $this;
	}
	
	/**
	 * Filters out files that do not match the specified pattern. Patter delimiters are automated to "#".
	 * 
	 * @param string $pattern The pattern to negate.
	 * @param string $mods    Pattern modifiers.
	 * 
	 * @return Analyzer
	 */
	public function is($pattern, $mods = null)
	{
    	return $this->filter(function($file) use ($pattern, $mods) {
    	    return preg_match('#' . $pattern . '#' . $mods, $file->__toString());
    	});
	}
	
	/**
	 * Filters out files that match the specified pattern. Patter delimiters are automated to "#".
	 * 
	 * @param string $pattern The pattern to negate.
	 * @param string $mods    Pattern modifiers.
	 * 
	 * @return Analyzer
	 */
	public function not($pattern, $mods = null)
	{
    	return $this->filter(function($file) use ($pattern, $mods) {
            return !preg_match('#' . $pattern . '#' . $mods, $file->__toString());
    	});
	}
	
	/**
	 * Filters the files based on the specified closure return value. If the closure returns false the file is not kept.
	 * Any other return value it is. The first argument passed to the closure is the file instance.
	 * 
	 * @param Closure $filter The filter.
	 * 
	 * @return Analyzer
	 */
	public function filter(Closure $filter)
	{
	    foreach ($this->files as $index => $file) {
	        if ($filter($file) === false) {
	            unset($this->files[$index]);
	        }
	    }
	    return $this;
	}
	
	/**
	 * Returns the files that are fully tested.
	 * 
	 * @return ArrayIterator
	 */
	public function getTestedFiles()
	{
    	$files = new ArrayIterator;
    	foreach ($this->files as $file) {
    	    if ($file->isTested()) {
    	        $files->offsetSet(null, $file);
    	    }
    	}
    	return $files;
	}
	
	/**
	 * Returns the files that are not fully tested.
	 * 
	 * @return ArrayIterator
	 */
	public function getUntestedFiles()
	{
    	$files = new ArrayIterator;
    	foreach ($this->files as $file) {
        	if ($file->isUntested()) {
            	$files->offsetSet(null, $file);
        	}
    	}
    	return $files;
	}
	
	/**
	 * Returns the files that are never executed.
	 * 
	 * @return ArrayIterator
	 */
	public function getDeadFiles()
	{
    	$files = new ArrayIterator;
    	foreach ($this->files as $file) {
        	if ($file->isDead()) {
            	$files->offsetSet(null, $file);
        	}
    	}
    	return $files;
	}

	/**
	 * Returns the number of files that were not fully tested.
	 * 
	 * @return int
	 */
	public function getUntestedFileCount()
	{
	    return $this->getUntestedFiles()->count();
	}

	/**
	 * Returns the number of files that were fully tested.
	 * 
	 * @return int
	 */
	public function getTestedFileCount()
	{
	    return $this->getTestedFiles()->count();
	}

	/**
	 * Returns the number of files that were not executed.
	 * 
	 * @return int
	 */
	public function getDeadFileCount()
	{
	    return $this->getDeadFiles()->count();
	}
	
	/**
	 * Returns the number of lines in all files.
	 * 
	 * @return int
	 */
	public function getLineCount()
	{
    	return $this->getSumOf('count');
    }
    
    /**
     * Returns the number of executed lines in all files.
     * 
     * @return int
     */
    public function getExecutedLineCount()
    {
        return $this->getSumOf('getExecutedLineCount');
    }
    
    /**
     * Returns the number of unexecuted lines in all files.
     * 
     * @return int
     */
    public function getUnexecutedLineCount()
    {
        return $this->getSumOf('getUnexecutedLineCount');
    }
    
    /**
     * Returns the number of dead lines in all files.
     * 
     * @return int
     */
    public function getDeadLineCount()
    {
        return $this->getSumOf('getDeadLineCount');
    }

	/**
	 * Gets the code coverage percentage.
	 * 
	 * @param int $precision The number to round to.
	 * 
	 * @return float
	 */
	public function getPercentTested($precision = 0)
	{
	    $sum     = $this->getSumOf('getPercentTested');
	    $all     = $this->count() * 100;
	    $percent = $sum / $all * 100;
	    
	    return round(number_format($percent, $precision), $precision);
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