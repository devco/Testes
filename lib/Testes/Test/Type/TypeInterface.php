<?php

namespace Testes\Test\Type;

interface TypeInterface
{
	/**
	 * Sets up the test.
	 * 
	 * @return void
	 */
	public function setUp();

	/**
	 * Tears down the test.
	 * 
	 * @return void
	 */
	public function tearDown();
	
	/**
	 * Returns all assertions asserted in this test class.
	 * 
	 * @return array
	 */
	public function getAssertions();

	/**
	 * Creates an assertion.
	 * 
	 * @param bool   $expression  The expression to test.
	 * @param string $description The description of the assertion.
	 * @param int    $code        A code if necessary.
	 * 
	 * @return \Testes\UnitTest\Test
	 */
	public function assert($expression, $description = null, $code = Assertion::DEFAULT_CODE);

	/**
	 * Logs the assertion and if it fails, a fatal assertion is thrown and the test exists.
	 * 
	 * @param bool   $expression  The expression to test.
	 * @param string $description The description of the assertion.
	 * @param int    $code        A code if necessary.
	 * 
	 * @return \Testes\UnitTest\Test
	 */
	public function assertFatal($expression, $description, $code = Assertion::DEFAULT_CODE);
}