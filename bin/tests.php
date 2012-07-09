<?php

use Testes\Coverage\Coverage;
use Testes\Finder\Finder;
use Testes\Autoloader;

$base = __DIR__ . '/..';

require $base . '/lib/Testes/Autoloader.php';

Autoloader::register();

$analyzer = new Coverage;
$analyzer->start();

$suite = new Finder($base, $test);
$suite = $suite->run();

$analyzer = $analyzer->stop();
$analyzer->addDirectory($base . '/lib');
$analyzer->is('\.php$');

?>

<?php if ($suite->getAssertions()->isPassed()): ?>
All tests passed!
<?php else: ?>
Tests failed:
<?php foreach ($suite->getAssertions()->getFailed() as $ass): ?>
  <?php echo $ass->getTestClass(); ?>
<?php endforeach; ?>
<?php endif; ?>

Coverage: <?php echo $analyzer->getPercentTested(); ?>%

