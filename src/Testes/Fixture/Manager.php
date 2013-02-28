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

    private $errors = [];

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

    public function getErrors()
    {
        return $this->errors;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->fixtures);
    }

    public function set($name, FixtureInterface $fixture)
    {
        $this->validate($fixture);

        $class = get_class($fixture);

        if (!isset($this->added[$class])) {
            $this->added[$class]   = $name;
            $this->fixtures[$name] = $fixture;
        }

        return $this;
    }

    public function get($name)
    {
        if (isset($this->fixtures[$name])) {
            return $this->fixtures[$name];
        }

        throw new InvalidArgumentException(sprintf('The fixture "%s" does not exist.', $name));
    }

    public function has($name)
    {
        return isset($this->fixtures[$name]);
    }

    public function remove($name)
    {
        if (isset($this->fixtures[$name])) {
            unset($this->fixtures[$name]);
        }

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

            $this->initDependencies($name, $fixture);
            $this->invoke($fixture, self::METHOD_INIT);

            $this->initialised[$name] = true;
        }

        unset($this->initialising[$name]);
    }

    private function initDependencies($name, FixtureInterface $fixture)
    {
        foreach ($this->resolveDependencies($fixture, self::METHOD_INIT) as $dependencyName => $dependencyInstance) {
            $this->initOne($dependencyName, $dependencyInstance);
        }
    }

    private function installOne($name, FixtureInterface $fixture)
    {
        if (isset($this->installing[$name])) {
            return;
        }

        if (!isset($this->installed[$name])) {
            $this->installing[$name] = true;

            $this->installDependencies($name, $fixture);
            $this->initOne($name, $fixture);
            $this->invoke($fixture, self::METHOD_INSTALL);
            unset($this->installing[$name]);

            $this->installed[$name] = true;
        }
    }

    private function installDependencies($name, FixtureInterface $fixture)
    {
        foreach ($this->resolveDependencies($fixture, self::METHOD_INIT) as $dependencyName => $dependencyInstance) {
            $this->installOne($dependencyName, $dependencyInstance);
        }
    }

    private function uninstallOne($name, FixtureInterface $fixture)
    {
        if (isset($this->uninstalling[$name])) {
            return;
        }

        if (isset($this->uninstalled[$name])) {
            $this->uninstallDependencies($name, $fixture);
        } else {
            $this->uninstalling[$name] = true;

            $this->uninstallDependants($name, $fixture);
            $this->initOne($name, $fixture);
            $this->invoke($fixture, self::METHOD_UNINSTALL);
            unset($this->uninstalling[$name]);

            $this->uninstalled[$name] = true;
        }
    }

    private function uninstallDependants($name, FixtureInterface $fixture)
    {
        foreach ($this->resolveDependants($fixture, self::METHOD_INIT) as $dependencyName => $dependencyInstance) {
            $this->uninstallOne($dependencyName, $dependencyInstance);
        }
    }

    private function uninstallDependencies($name, FixtureInterface $fixture)
    {
        foreach ($this->resolveDependencies($fixture, self::METHOD_INIT) as $dependencyName => $dependencyInstance) {
            $this->uninstallOne($dependencyName, $dependencyInstance);
        }
    }

    private function invoke(FixtureInterface $fixture, $method)
    {
        set_error_handler($this->generateErrorHandler($fixture, $method));

        try {
            call_user_func_array(
                [$fixture, $method],
                $this->resolveDependencies($fixture, $method)
            );
        } catch (Exception $e) {
            $this->errors[get_class($fixture) . '::' . $method . '()'] = $e;
        }

        restore_error_handler();

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

            if (!isset($this->added[$dependencyName])) {
                $this->set($dependencyName, $dependency->newInstance());
            }

            $dependencies[$this->added[$dependencyName]] = $this->fixtures[$this->added[$dependencyName]];
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

    private function generateErrorHandler(FixtureInterface $fixture, $method)
    {
        return function($errno, $errstr, $errfile, $errline, $errcontext) use ($fixture, $method) {
            throw new RuntimeException(sprintf(
                'Error calling "%s" on fixture "%s" because: "%s" in "%s" on line "%s".',
                $method,
                get_class($fixture),
                $errstr,
                $errfile,
                $errline
            ));
        };
    }
}