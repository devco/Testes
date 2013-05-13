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

    private $installed = [];

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
        foreach ($this->fixtures as $fixture) {
            $this->installOne($fixture);
        }

        return $this;
    }

    public function uninstall()
    {
        foreach ($this->installed as $fixture) {
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
        $name = get_class($fixture);

        if (!isset($this->initialised[$name])) {
            $this->initialised[$name] = $fixture;
            $this->initDependencies($fixture);
            $this->invoke($fixture, self::METHOD_INIT);
        }
    }

    private function installOne(FixtureInterface $fixture)
    {
        $name = get_class($fixture);

        if (!isset($this->installed[$name])) {
            $this->installed[$name] = $fixture;
            $this->installDependencies($fixture);
            $this->initOne($fixture);
            $this->invoke($fixture, self::METHOD_INSTALL);
        }
    }

    private function uninstallOne(FixtureInterface $fixture)
    {

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

    private function uninstallDependencies(FixtureInterface $fixture)
    {
        foreach ($this->resolveDependencies($fixture) as $dependencyInstance) {
            $this->uninstallOne($dependencyInstance);
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

    private function resolveDependants(FixtureInterface $fixture, $method = self::METHOD_INIT)
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

    private function resolveDependencies(FixtureInterface $fixture, $method = self::METHOD_INIT)
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
