<?php

namespace Testes\Fixture;
use InvalidArgumentException;
use ReflectionMethod;
use RuntimeException;

class Manager implements ManagerInterface
{
    const METHOD_INIT = 'init';

    const METHOD_INSTALL = 'install';

    const METHOD_UNINSTALL = 'uninstall';

    private $installed = [];

    public function count()
    {
        return count($this->installed);
    }

    public function getIterator()
    {
        return new ArrayIterator($this->installed);
    }

    public function init(FixtureInterface $fixture)
    {
        $this->callMethod($fixture, self::METHOD_INIT);
        return $this;
    }

    public function install(FixtureInterface $fixture)
    {
        if (!$this->installed($fixture)) {
            $this->installed[get_class($fixture)] = $fixture;
            $this->callMethod($fixture, self::METHOD_INSTALL);
        }

        return $this;
    }

    public function uninstall(FixtureInterface $fixture)
    {
        if ($this->installed($fixture)) {
            unset($this->installed[get_class($fixture)]);
            $this->callMethod($fixture, self::METHOD_UNINSTALL);
        }

        return $this;
    }

    public function installed(FixtureInterface $fixture)
    {
        return in_array(get_class($fixture), $this->installed);
    }

    public function initAll(array $fixtures)
    {
        foreach ($fixtures as $fixture) {
            $this->init($fixture);
        }

        return $this;
    }

    public function installAll(array $fixtures)
    {
        foreach ($fixtures as $fixture) {
            $this->install($fixture);
        }

        return $this;
    }

    public function uninstallAll()
    {
        foreach ($this->installed as $fixture) {
            $this->uninstall($fixture);
        }

        return $this;
    }

    private function callMethod(FixtureInterface $fixture, $method)
    {
        if (!method_exists($fixture, $method)) {
            throw new RuntimeException(sprintf(
                'Cannot call method "%s" for fixture "%s" because it does not exist.',
                $method,
                get_class($fixture)
            ));
        }

        $dependencies = $this->getMethodParams($fixture, $method);

        $this->initAll($dependencies);

        return call_user_func_array([$fixture, $method], $dependencies);
    }

    private function getMethodParams(FixtureInterface $fixture, $method)
    {
        $method = new ReflectionMethod($fixture, $method);
        $params = [];

        foreach ($method->getParameters() as $index => $param) {
            $class = $param->getClass();

            if ($class->implementsInterface('Testes\Fixture\FixtureInterface')) {
                $class    = $class->newInstance();
                $params[] = $class;
            } else {
                throw new InvalidArgumentException(sprintf(
                    'Parameter %d in method "%s" for setting up the fixture "%s" must implement interface "Testes\Fixture\FixtureInterface".',
                    $index,
                    $method,
                    $fixtureName
                ));
            }
        }

        return $params;
    }
}