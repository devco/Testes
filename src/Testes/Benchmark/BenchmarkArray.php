<?php

namespace Testes\Benchmark;
use ArrayIterator;
use Countable;
use IteratorAggregate;

class BenchmarkArray implements BenchmarkInterface, Countable, IteratorAggregate
{
    private $benchmarks = [];

    public function count()
    {
        return count($this->benchmarks);
    }

    public function getIterator()
    {
        return new ArrayIterator($this->benchmarks);
    }

    public function add($name, BenchmarkInterface $benchmark)
    {
        $this->benchmarks[$name] = $benchmark;
        return $this;
    }

    public function get($name)
    {
        if (isset($this->benchmarks[$name])) {
            return $this->benchmarks[$name];
        }

        throw new InvalidArgumentException(sprintf('The benchmark "%s" does not exist.', $name));
    }

    public function has($name)
    {
        return isset($this->benchmarks[$name]);
    }

    public function getTime()
    {
        return $this->sum(__METHOD__);
    }

    public function getStartTime()
    {
        return $this->sum(__METHOD__);
    }

    public function getStopTime()
    {
        return $this->sum(__METHOD__);
    }

    public function getMemory()
    {
        return $this->sum(__METHOD__);
    }

    public function getStartMemory()
    {
        return $this->sum(__METHOD__);
    }

    public function getStopMemory()
    {
        return $this->sum(__METHOD__);
    }

    private function sum($method)
    {
        $sum = 0;

        foreach ($this->benchmarks as $benchmark) {
            $sum += $benchmark->$method();
        }

        return $sum;
    }
}