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

    private $added = [];

    private $dependencies = [];

    private $fixtures = [];

    private $initialised = [];

    private $initialising = [];

    private $installed = [];

    private $installing = [];

    private $uninstalled = [];

    private $uninstalling = [];

    private $methods = [
        self::METHOD_INIT,
        self::METHOD_INSTALL,
        self::METHOD_UNINSTALL
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

        $class = get_class($fixture);

        $this->added[$name]  = $class;
        $this->added[$class] = $class;

        if (!isset($this->fixtures[$class])) {
            $this->fixtures[$class] = $fixture;
        }

        return $this;
    }

    public function get($name)
    {
        if (isset($this->added[$name])) {
            $name = $this->added[$name];
        }

        if (isset($this->fixtures[$name])) {
            return $this->fixtures[$name];
        }

        throw new InvalidArgumentException(sprintf('The fixture "%s" does not exist.', $name));
    }

    public function has($name)
    {
        return isset($this->added[$name]);
    }

    public function remove($name)
    {
        if (!isset($this->added[$name])) {
            return $this;
        }

        $class  = $this->added[$name];
        $remove = [];

        foreach ($this->added as $k => $v) {
            if ($class === $v) {
                $remove[] = $k;
            }
        }

        foreach ($remove as $v) {
            unset($this->added[$v]);
        }

        unset($this->fixtures[$class]);

        return $this;
    }

    public function install()
    {
        foreach ($this->fixtures as $name => $fixture) {
            $this->installOne($name, $fixture);
        }

        return $this;
    }

    public function uninstall()
    {
        foreach ($this->fixtures as $name => $fixture) {
            $this->uninstallOne($name, $fixture);
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

    private function initOne($name, FixtureInterface $fixture)
    {
        if (isset($this->initialising[$name])) {
            return;
        }

        if (!isset($this->initialised[$name])) {
            $this->initialising[$name] = true;

            $this->initDependencies($fixture);
            $this->invoke($fixture, self::METHOD_INIT);

            $this->initialised[$name] = true;
        }

        unset($this->initialising[$name]);
    }

    private function installOne($name, FixtureInterface $fixture)
    {
        if (isset($this->installing[$name])) {
            return;
        }

        if (!isset($this->installed[$name])) {
            $this->installing[$name] = true;

            $this->installDependencies($fixture);
            $this->initOne($name, $fixture);
            $this->invoke($fixture, self::METHOD_INSTALL);
            unset($this->installing[$name]);

            $this->installed[$name] = true;
        }
    }

    private function uninstallOne($name, FixtureInterface $fixture)
    {
        if (isset($this->uninstalling[$name])) {
            return;
        }

        if (isset($this->uninstalled[$name])) {
            $this->uninstallDependencies($fixture);
        } else {
            $this->uninstalling[$name] = true;

            $this->uninstallDependants($fixture);
            $this->initOne($name, $fixture);
            $this->invoke($fixture, self::METHOD_UNINSTALL);
            unset($this->uninstalling[$name]);

            $this->uninstalled[$name] = true;
        }
    }

    private function initDependencies(FixtureInterface $fixture)
    {
        foreach ($this->resolveDependencies($fixture, self::METHOD_INIT) as $dependencyName => $dependencyInstance) {
            $this->initOne($dependencyName, $dependencyInstance);
        }
    }

    private function installDependencies(FixtureInterface $fixture)
    {
        foreach ($this->resolveDependencies($fixture, self::METHOD_INIT) as $dependencyName => $dependencyInstance) {
            $this->installOne($dependencyName, $dependencyInstance);
        }
    }

    private function uninstallDependants(FixtureInterface $fixture)
    {
        foreach ($this->resolveDependants($fixture, self::METHOD_INIT) as $dependencyName => $dependencyInstance) {
            $this->uninstallOne($dependencyName, $dependencyInstance);
        }
    }

    private function uninstallDependencies(FixtureInterface $fixture)
    {
        foreach ($this->resolveDependencies($fixture, self::METHOD_INIT) as $dependencyName => $dependencyInstance) {
            $this->uninstallOne($dependencyName, $dependencyInstance);
        }
    }

    private function invoke(FixtureInterface $fixture, $method)
    {
        call_user_func_array(
            [$fixture, $method],
            $this->resolveDependencies($fixture, $method)
        );

        return $this;
    }

    private function resolveDependants(FixtureInterface $fixture, $method)
    {
        $name       = $this->added[get_class($fixture)];
        $dependants = [];

        foreach ($this->fixtures as $dependantName => $dependantInstance) {
            $dependencies = $this->resolveDependencies($dependantInstance, $method);

            if (isset($dependencies[$name])) {
                $dependants[$dependantName] = $dependantInstance;
            }
        }

        return $dependants;
    }

    private function resolveDependencies(FixtureInterface $fixture, $method)
    {
        $method       = new ReflectionMethod($fixture, $method);
        $dependencies = [];

        foreach ($method->getParameters() as $param) {
            $dependency     = $param->getClass();
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