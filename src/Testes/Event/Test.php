<?php

namespace Testes\Event;

use \Nekoo\EventEmitter;
use Testes\Test\UnitAbstract;


class Test {
    use EventEmitter;

    public function preMethod($methodName, UnitAbstract $test) {
        $this->emit('preMethod', $methodName, $test);
    }

    public function postMethod($methodName, UnitAbstract $test) {
        $this->emit('postMethod', $methodName, $test);
    }

    public function preRun(UnitAbstract $test) {
        $this->emit('preRun', $test);
    }

    public function postRun(UnitAbstract $test) {
        $this->emit('postRun', $test);
    }
}