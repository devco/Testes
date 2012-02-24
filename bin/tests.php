<?php

use Testes\Autoloader\Autoloader;
use Testes\Coverage\Analyzer;
use Testes\Coverage\Coverage;

ini_set('display_errors', 'on');
error_reporting(E_ALL ^ E_STRICT);

$lib = dirname(__FILE__);
$dir = $lib . '/../tests';

require $lib . '/../lib/Testes/Autoloader/Autoloader.php';
Autoloader::register($dir);

// set up the test renderer and runner
$type  = isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : 'cli';
$type  = ucfirst($type);
$type  = '\Testes\Renderer\\' . $type;
$type  = new $type;
$tests = new Test;

// start covering tests
$coverage = new Coverage;
$coverage->start();

// run the tests
$tests = new Test;
$tests->run();

// stop coverage
$coverage = $coverage->stop();

// analyze and output code coverage
$analyzer = new Analyzer($coverage);
$analyzer->addDirectory($dir);

// output test results
echo $type->render($tests);

// output coverage
echo 'Coverage: '
    . $analyzer->getPercentage()
    . '% of lines across '
    . count($analyzer->getTestedFiles())
    . ' of '
    . (count($analyzer->getTestedFiles()) + count($analyzer->getUntestedFiles()))
    . ' files.'
    . "\n";
