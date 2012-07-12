<?php

namespace Testes\Test;
use Testes\RunableInterface;

/**
 * Base test interface.
 * 
 * @category UnitTesting
 * @package  Testes
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
interface TestInterface extends RunableInterface
{
    /**
	 * Creates an assertion.
	 * 
	 * @param bool   $expression  The expression to test.
	 * @param string $description The description of the assertion.
	 * @param int    $code        A code if necessary.
	 * 
	 * @return TestInterface
	 */
	public function assert($expression, $description = null, $code = Assertion::DEFAULT_CODE);
}