<?php

use Testes\Autoloader\Autoloader;

ini_set('display_errors', 'on');
error_reporting(E_ALL ^ E_STRICT);

$lib = dirname(__FILE__);
require $lib . '/../lib/Testes/Autoloader/Autoloader.php';
Autoloader::register($lib . '/../tests');

$test = new Test;
$type = isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : 'cli';
$type = ucfirst($type);
$type = '\Testes\Renderer\\' . $type;
$type  = new $type;
echo $type->render($test->run());