<?php

use Testes\Coverage\Coverage;
use Testes\Finder\Finder;
use Testes\Autoloader;

$base = __DIR__ . '/..';

require $base . '/lib/Testes/Autoloader.php';

Autoloader::register();

// so we can get some helpful data
$analyzer = new Coverage;
$analyzer->start();

// re-run the test b/c two xdebug functions can't be run at the same time
$suite = new Finder($base, $test);
$suite = $suite->run();

// gather useful data
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

