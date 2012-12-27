<?php

namespace Testes\Benchmark;

interface BenchmarkInterface
{
    public function getTime();

    public function getStartTime();

    public function getStopTime();

    public function getMemory();

    public function getStartMemory();

    public function getStopMemory();
}