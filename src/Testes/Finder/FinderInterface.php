<?php

namespace Testes\Finder;
use IteratorAggregate;

/**
 * Finder interface.
 * 
 * @category UnitTesting
 * @package  Testes
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
interface FinderInterface extends IteratorAggregate
{
	/**
	 * Creates a suite, runs the tests and returns the suite that was run.
	 * 
	 * @return Suite
	 */
	public function run();
}