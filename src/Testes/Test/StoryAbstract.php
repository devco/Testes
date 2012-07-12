<?php

namespace Testes\Test;
use LogicException;

/**
 * Allows the describing of a test scenario that results in assertions.
 * 
 * @category UnitTesting
 * @package  Testes
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
abstract class StoryAbstract extends UnitAbstract
{
	/**
	 * Describes the subject of the test.
	 * 
	 * @param string $given The description.
	 * 
	 * @return StoryAbstract
	 */
	public function given($given)
	{
		return $this->call('given', $given, func_get_args());
	}
	
	/**
	 * Describes a modification to the subject.
	 * 
	 * @param string $when The modification.
	 * 
	 * @return StoryAbstract
	 */
	public function when($when)
	{
		return $this->call('when', $when, func_get_args());
	}
	
	/**
	 * Describes the result.
	 * 
	 * @param string $then The result.
	 * 
	 * @return StoryAbstract
	 */
	public function then($then)
	{
		return $this->call('then', $then, func_get_args());
	}
	
	/**
	 * Calls a method that corresponds to the scenario method description.
	 * 
	 * @param string $type   The method type.
	 * @param string $method The method name.
	 * @param array  $args   The arguments to pass.
	 * 
	 * @return StoryAbstract
	 */
	private function call($type, $method, array $args = array())
	{
		// the first item will be the type of call so remove it
		array_shift($args);
		
		// format the method
		$method = $this->format($method);
		$method = $type . $method;
		
		// check if the method exists
		if (!method_exists($this, $method)) {
		    throw new LogicException('You did not define a test method for "' . $method . '".');
		}
		
		// call the method
		call_user_func_array(array($this, $method), $args);
		
		return $this;
	}
	
	/**
	 * Formats the description into a method name.
	 * 
	 * @param string $str The description to format.
	 * 
	 * @return string
	 */
	private function format($str)
	{
		$str = preg_replace('/[^a-zA-Z]/', ' ', $str);
		$str = ucwords($str);
		$str = str_replace(' ', '', $str);
		return $str;
	}
}