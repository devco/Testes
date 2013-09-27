<?php

use Testes\Coverage\Coverage;
use Testes\Finder\Finder;
use Testes\Autoloader;
use Testes\Event;

$base = __DIR__ . '/..';

require $base . '/src/Testes/Autoloader.php';

Autoloader::register();
Autoloader::addPath($base . '/tests');
Autoloader::addPath($base . '/src');
Autoloader::addPath($base . '/vendor/devco');
Autoloader::addPath($base . '/vendor/devco/event-emitter/src');

$coverage = new Coverage;
$finder   = new Finder($base . '/tests', 'Test');

$coverage->start();

echo PHP_EOL;

$event = new Event\Test;

$event->on('postRun', function($test) {
    echo $test->getAssertions()->isPassed() && !$test->getExceptions()->count() ? '.' : 'F';
});

$suite = $finder->run($event);

echo PHP_EOL . PHP_EOL . sprintf('Ran %d test%s.', count($suite), count($suite) === 1 ? '' : 's');

$analyzer = $coverage->stop()->addDirectory($base . '/src')->is('\.php$');

echo PHP_EOL . PHP_EOL . 'Coverage: ' . $analyzer->getPercentTested() . '%' . PHP_EOL . PHP_EOL;

if (count($assertions = $suite->getAssertions()->getFailed())) {
    echo 'Assertions' . PHP_EOL;
    echo '----------' . PHP_EOL;

    foreach ($assertions as $ass) {
        echo '  ' . $ass->getTestClass() . ':' . $ass->getTestLine() . ' ' . $ass->getMessage() . PHP_EOL;
    }

    echo PHP_EOL;
}

if (count($exceptions = $suite->getExceptions())) {
    echo 'Exceptions' . PHP_EOL;
    echo '----------' . PHP_EOL;

    foreach ($exceptions as $exc) {
        echo '  ' . $exc->getTestFile() . ':' . $exc->getTestLine() . ' ' . $exc->getMessage() . PHP_EOL;
    }

    echo PHP_EOL;
}

if (count($benchmarks = $suite->getBenchmarks())) {
    echo 'Benchmarks' . PHP_EOL;
    echo '----------' . PHP_EOL;

    foreach ($benchmarks as $name => $bench) {
        echo '  ' . $name . ': ' . round($bench->getTime(), 3) . PHP_EOL;
    }

    echo PHP_EOL;
}