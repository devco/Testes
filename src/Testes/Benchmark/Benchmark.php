<?php

namespace Testes\Benchmark;

class Benchmark implements BenchmarkInterface
{
    private $time = 0;

    private $memory = 0;

    private $startTimee = 0;

    private $stopTime = 0;

    private $startMemory = 0;

    private $stopMemory = 0;

    private $peakMemory = 0;

    public function start()
    {
        $this->startTimer();
        $this->startMemoryCounter();
        return $this;
    }

    public function stop()
    {
        $this->stopTimer();
        $this->stopMemoryCounter();
        return $this;
    }

    public function getTime()
    {
        return $this->time;
    }

    public function getStartTime()
    {
        return $this->startTime;
    }

    public function getStopTime()
    {
        return $this->stopTime;
    }

    public function getMemory()
    {
        return $this->memory;
    }

    public function getStartMemory()
    {
        return $this->startMemory;
    }

    public function getStopMemory()
    {
        return $this->stopMemory;
    }

    private function startTimer()
    {
        $this->startTime = microtime(true);
        return $this;
    }

    private function stopTimer()
    {
        $this->stopTime = microtime(true);
        $this->time     = $this->stopTime - $this->startTime;
        return $this;
    }

    private function startMemoryCounter()
    {
        $this->startMemory = memory_get_usage();
        return $this;
    }

    private function stopMemoryCounter()
    {
        $this->stopMemory = memory_get_usage();
        $this->peakMemory = memory_get_peak_usage();
        $this->memory     = $this->peakMemory - $this->startMemory;
        return $this;
    }
}