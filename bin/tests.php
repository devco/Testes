<?php

use Testes\Autoloader;
use Testes\Coverage\Analyzer;
use Testes\Coverage\Coverage;
use Testes\Renderer\Cli;
use Testes\Test\Finder\Finder;
use Testes\Test\Reporter\Reporter;
use Testes\Test\Runner\Runner;

ini_set('display_errors', 'on');
error_reporting(E_ALL ^ E_STRICT);

$lib = dirname(__FILE__) . '/../lib';

require $lib . '/Testes/Autoloader.php';
Autoloader::register();

// start covering tests
$coverage = new Coverage;
$coverage->start();

// configure the reporter
$reporter = new Reporter;

// configure the finder
$finder = new Finder($lib . '/../tests/Test', 'Test');

// run the tests
$tests = new Runner($reporter);
$tests->run($finder);

// stop coverage and analyze coverage
$analyzer = new Analyzer($coverage->stop());
$analyzer->addDirectory($lib);

// output test results
$out = new Cli;
echo $out->render($reporter);

// output coverage
echo 'Coverage: '
    . $analyzer->getPercentage()
    . '% of lines across '
    . count($analyzer->getTestedFiles())
    . ' of '
    . (count($analyzer->getTestedFiles()) + count($analyzer->getUntestedFiles()))
    . ' files.'
    . "\n";
