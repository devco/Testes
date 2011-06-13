<?php

use Testes\Autoloader;

$lib = dirname(__FILE__);
require $lib . '/../lib/Testes/Autoloader.php';
Autoloader::register($lib . '/../tests');

$test = new Test;
$type = isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : 'cli';
$type = ucfirst($type);
$type = '\Testes\Output\\' . $type;
$type  = new $type;
echo $type->render($test->run());