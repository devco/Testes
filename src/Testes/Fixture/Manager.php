<?php

namespace Testes\Fixture;
use ArrayIterator;
use InvalidArgumentException;
use ReflectionMethod;
use RuntimeException;

class Manager implements ManagerInterface
{
    const FLAG_INITIALIZED = 'initialized';

    const FLAG_INSTALLED = 'installed';

    const METHOD_INIT = 'init';

    const METHOD_INSTALL = 'install';

    const METHOD_UNINSTALL = 'uninstall';

    private $fixtures = [];

    private static $flags = [
        self::FLAG_INITIALIZED => [],
        self::FLAG_INSTALLED   => []
    ];

    private static $methods = [
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

    public function toArray()
    {
        return $this->fixtures;
    }

    public function set($name, FixtureInterface $fixture)
    {
        $this->validate($fixture);

        $this->fixtures[$name] = $fixture;

        if (!$this->initialized($name)) {
            $this->flag($name, self::FLAG_INITIALIZED);
            $this->invoke($name, self::METHOD_INIT);
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
            if (!$this->installed($name)) {
                $this->flag($name, self::FLAG_INSTALLED);
                $this->invoke($name, self::METHOD_INSTALL);
            }
        }

        return $this;
    }

    public function uninstall()
    {
        foreach (array_reverse($this->fixtures) as $name => $fixture) {
            if ($this->installed($name)) {
                $this->unflag($name, self::FLAG_INSTALLED);
                $this->invoke($name, self::METHOD_UNINSTALL);
            }
        }

        return $this;
    }

    public function initialized($name)
    {
        return $this->flagged($name, self::FLAG_INITIALIZED);
    }

    public function installed($name)
    {
        return $this->flagged($name, self::FLAG_INSTALLED);
    }

    private function flag($name, $as)
    {
        self::$flags[$as][get_class($this->fixtures[$name])] = $this->fixtures[$name];
        return $this;
    }

    private function unflag($name, $from)
    {
        unset(self::$flags[$from][get_class($this->fixtures[$name])]);
        return $this;
    }

    private function flagged($name, $as)
    {
        return isset(self::$flags[$as][get_class($this->fixtures[$name])]);
    }

    private function invoke($name, $method)
    {
        call_user_func_array(
            [$this->fixtures[$name], $method],
            $this->resolveDependencies($name, $method)->toArray()
        );

        return $this;
    }

    private function resolveDependencies($name, $method)
    {
        $fixture = $this->fixtures[$name];
        $method  = new ReflectionMethod($fixture, $method);
        $manager = new self;

        foreach ($method->getParameters() as $index => $param) {
            $class = $param->getClass();

            if ($class->implementsInterface('Testes\Fixture\FixtureInterface')) {
                $manager->set(lcfirst($class->getName()), $class->newInstance());
            } else {
                throw new InvalidArgumentException(sprintf(
                    'Parameter %d in method "%s" for setting up the fixture "%s" must implement interface "Testes\Fixture\FixtureInterface".',
                    $index,
                    $method,
                    $class->getName()
                ));
            }
        }

        return $manager;
    }

    private function validate(FixtureInterface $fixture)
    {
        foreach (self::$methods as $method) {
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