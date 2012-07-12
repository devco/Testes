<?php

namespace Testes\Coverage;
use RuntimeException;

/**
 * Handles code coverage.
 * 
 * @category UnitTesting
 * @package  Testes
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
class Coverage
{
	/**
	 * Sets up a new coverage manager.
	 * 
	 * @return Coverage
	 */
	public function __construct()
	{
		// ensure that XDEBUG is enabled
		if (!function_exists('xdebug_start_code_coverage')) {
			throw new RuntimeException('You must have the XDEBUG extension installed in order to analyze code coverage.');
		}

		// ensure that XDEBUG code coverage is enabled
		ini_set('xdebug.coverage_enable', 1);
	}

	/**
	 * Starts covering code.
	 * 
	 * @return Coverage
	 */
	public function start()
	{
		$this->stop();
		xdebug_start_code_coverage(XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE);
		return $this;
	}
	
	/**
	 * Pauses code coverage.
	 * 
	 * @return Coverage
	 */
	public function pause()
	{
		xdebug_stop_code_coverage(false);
		return $this;
	}
	
	/**
	 * Stops covering code and returns the analyzer with the result.
	 * 
	 * @return Analyzer
	 */
	public function stop()
	{
		$analyzer = $this->analyze();
		xdebug_stop_code_coverage();
		return $analyzer;
	}

	/**
	 * Returns the analyzer but does not stop code coverage.
	 * 
	 * @return Analyzer
	 */
	public function analyze()
	{
		return new Analyzer(new CoverageResult(xdebug_get_code_coverage()));
	}
}