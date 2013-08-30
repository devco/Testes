<?php

namespace Testes\Fixture;
use ArrayIterator;
use Exception;
use InvalidArgumentException;
use ReflectionMethod;
use RuntimeException;

class Manager implements ManagerInterface
{
    const METHOD_INIT = 'init';

    const METHOD_INSTALL = 'install';

    const METHOD_UNINSTALL = 'uninstall';

    const METHOD_INSTALLED = 'installed';

    private $registry = [];

    private $dependencies = [];

    private $fixtures = [];

    private $initialised = [];

    private $methods = [
        self::METHOD_INIT,
        self::METHOD_INSTALL,
        self::METHOD_UNINSTALL,
        self::METHOD_INSTALLED
    ];

    public function count()
    {
        return count($this->fixtures);
    }

    public function getIterator()
    {
        return new ArrayIterator($this->fixtures);
    }

    public function set($name, FixtureInterface $fixture)
    {
        $this->validate($fixture);

        $this->registry[$name] = $fixture;

        if (!in_array($fixture, $this->fixtures)) {
            $this->fixtures[get_class($fixture)] = $fixture;
        }

        return $this;
    }

    public function get($name)
    {
        if (isset($this->registry[$name])) {
            return $this->registry[$name];
        }

        throw new InvalidArgumentException(sprintf('Cannot get fixture "%s" because it does not exist.', $name));
    }

    public function has($name)
    {
        return isset($this->registry[$name]);
    }

    public function remove($name)
    {
        if (isset($this->registry[$name])) {
            unset($this->registry[$name]);
            return $this;
        }

        throw new InvalidArgumentException(sprintf('Cannot remove fixture "%s" because it does not exist.', $name));
    }

    public function install()
    {
        foreach ($this->fixtures as $fixture) {
            $this->installOne($fixture);
        }

        return $this;
    }

    public function uninstall()
    {
        foreach ($this->fixtures as $fixture) {
            $this->uninstallOne($fixture);
        }

        return $this;
    }

    public function getDependencyTree()
    {
        $tree = [];

        foreach ($this->fixtures as $name => $fixture) {
            $tree[$name] = $this->getDependencyTreeFor($fixture);
        }

        return $tree;
    }

    private function getDependencyTreeFor(FixtureInterface $fixture)
    {
        $dependencies = [];

        foreach ($this->resolveDependencies($fixture, self::METHOD_INIT) as $name => $dependency) {
            $dependencies[$name] = $this->getDependencyTreeFor($dependency);
        }

        return $dependencies;
    }

    private function initOne(FixtureInterface $fixture)
    {
        if (!in_array($fixture, $this->initialised)) {
            $this->initialised[] = $fixture;
            $this->initDependencies($fixture);
            $this->invoke($fixture, self::METHOD_INIT);
        }
    }

    private function installOne(FixtureInterface $fixture)
    {
        $this->initOne($fixture);

        if (!$this->invoke($fixture, self::METHOD_INSTALLED)) {
            $this->installDependencies($fixture);
            $this->invoke($fixture, self::METHOD_INSTALL);
        }
    }

    private function uninstallOne(FixtureInterface $fixture)
    {
        $this->initOne($fixture);

        if ($this->invoke($fixture, self::METHOD_INSTALLED)) {
            $this->uninstallDependants($fixture);
            $this->invoke($fixture, self::METHOD_UNINSTALL);
        }
    }

    private function initDependencies(FixtureInterface $fixture)
    {
        foreach ($this->resolveDependencies($fixture) as $dependencyInstance) {
            $this->initOne($dependencyInstance);
        }
    }

    private function installDependencies(FixtureInterface $fixture)
    {
        foreach ($this->resolveDependencies($fixture) as $dependencyInstance) {
            $this->installOne($dependencyInstance);
        }
    }

    private function uninstallDependants(FixtureInterface $fixture)
    {
        foreach ($this->resolveDependants($fixture) as $dependencyInstance) {
            $this->uninstallOne($dependencyInstance);
        }
    }

    private function invoke(FixtureInterface $fixture, $method)
    {
        try {
            return call_user_func_array(
                [$fixture, $method],
                $this->resolveDependencies($fixture, $method)
            );
        } catch (Exception $e) {
            throw new RuntimeException(sprintf(
                'Cannot call "%s" on fixture "%s" because: %s',
                $method,
                get_class($fixture),
                $e->getMessage()
            ));
        }
    }

    private function resolveDependants(FixtureInterface $fixture, $method = self::METHOD_INIT)
    {
        $name = get_class($fixture);
        $dependants = [];

        foreach ($this->fixtures as $dependantName => $dependantInstance) {
            $dependencies = $this->resolveDependencies($dependantInstance, $method);

            if (isset($dependencies[$name])) {
                $dependants[$dependantName] = $dependantInstance;
            }
        }

        return $dependants;
    }

    private function resolveDependencies(FixtureInterface $fixture, $method = self::METHOD_INIT)
    {
        $method = new ReflectionMethod($fixture, $method);
        $dependencies = [];

        foreach ($method->getParameters() as $param) {
            $dependency = $param->getClass();
            $dependencyName = $dependency->getName();

            if (!$dependency->implementsInterface('Testes\Fixture\FixtureInterface')) {
                throw new InvalidArgumentException(sprintf(
                    'Parameter %d in method "%s" for setting up the fixture "%s" must implement interface "Testes\Fixture\FixtureInterface".',
                    $param->getName(),
                    $method->getName(),
                    $dependencyName
                ));
            }

            if (!$this->has($dependencyName)) {
                $this->set($dependencyName, $dependency->newInstance());
            }

            $dependencies[$dependencyName] = $this->get($dependencyName);
        }

        return $dependencies;
    }

    private function validate(FixtureInterface $fixture)
    {
        foreach ($this->methods as $method) {
            if (!method_exists($fixture, $method)) {
                throw new RuntimeException(sprintf(
                    'The fixture "%s" must define the method "%s".',
                    get_class($fixture),
                    $method
                ));
            }
        }

        return $this;
    }
}