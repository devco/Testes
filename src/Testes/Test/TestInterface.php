<?php

namespace Testes\Test;
use Testes\RunableInterface;

interface TestInterface extends RunableInterface
{
	public function assert($expression, $description = null, $code = Assertion::DEFAULT_CODE);
}