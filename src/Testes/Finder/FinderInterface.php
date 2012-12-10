<?php

namespace Testes\Finder;
use IteratorAggregate;

interface FinderInterface extends IteratorAggregate
{
	public function run(callable $after = null);
}