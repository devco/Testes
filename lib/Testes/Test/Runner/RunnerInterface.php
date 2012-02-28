<?php

namespace Testes\Test\Runner;
use Testes\Test\Finder\FinderInterface;

class RunnerInterface
{
	public function run(FinderInterface $finder);
}