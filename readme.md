Introduction
------------

Testes - pardon my language - is a dead-simple testing framework written in PHP 5.4. Its goal is to facilitate a simple, fast and maintainable testing.

Setup
-----

To set up Testes, you need just need to make sure your tests are `psr-0` autoloader compatible and that the directory your tests are located in is registered as an autoload path.

Testes comes with an autoloader that can do this for you if you like:

    require '../lib/Testes/Autoloader/Autoloader.php';
    Testes\Autoloader\Autoloader::register('./tests');

After that, you just need to setup your test hierarchy. Really, all you need to do is make sure you've organised your tests so that it is easy for you, and your peers, to browser the tests.

- tests
    - Test
        - Subfolder
            - SomeTest.php

The `SomeTest` class may be defined as:

    namespace Test\Subfolder;
    use Testes\Test\UnitAbstract;
    
    class SomeTest extends UnitAbstract
    {
        ...
    }

Test Methods
------------

Test methods are any public methods that aren't in any parent classes or implemented interfaces. This means that `public` methods such as `setUp()` and `tearDown()` aren't valid test methods and won't be run as part of testing.

Some good test names:

- makeSureAllPropertiesAreSetWhenHydrating()
- testAssertionAggregation()
- completeRequestDispatchingTest()

You cannot define test methods as part of a test suite. They must exist in a test class.

There are two types of tests: *Unit Tests* and *Stories*.

### Unit Tests

Unit tests derive from the `Testes\Test\UnitAbstract` class. This is generally when you will be testing small units of code rather than behavior.

### Stories

Stories derive from `Testes\Test\StoryAbstract`. They follow the "given ... when ... then ..." convention for describing test cases. The methods that define these scenarios should be `public`. The methods that handle each part of the scenarios should be `protected`.

    <?php
    
    namespace Test;
    use Testes\Test\StoryAbstract;
    
    class MyStory extends StoryAbstract
    {
        private $request;
        
        private $error;
        
        public function ensureProperError()
        {
            $this->given('a bad request')->when('an error occurs')->then('an error should be returned');
        }
        
        protected function givenABadRequest()
        {
            $this->request = ...;
        }
        
        protected function whenAnErrorOccurs()
        {
            if (!$this->request) {
                $this->error = 'Some error message.';
            }
        }
        
        protected function thenAnErrorShouldBeReturned()
        {
            $this->assert($this->error && is_string($this->error));
        }
    }

Setting Up and Tearing Down
---------------------------

In your test classes, you are allowed to define `setUp` and `tearDown` methods for executing code before and after your test methods have executed, respectively. You can put whatever code that is necessary to prepare your tests for running and then cleaning up after them.

### Fixtures

Generally, what you're going to use these methods for are preparing data to run your tests against. This test data is usually called a fixture, or fixtures. Fixtures exist as a way to model your test data and to automate the `setUp` and `tearDown` in it instead of making you define it in your test class.

A fixture at a bare minimum, must define a method that generates its data:

    class MyFixture extends Testes\Fixture\FixtureAbstract
    {
        public static function generateData()
        {
            return [
                'id'   => 1,
                'name' => 'value'
            ];
        }
    }

You would now use your `setUp` method in your test class to add this fixture to the test:

    $this->setFixture('myfixture', new MyFixture);

This fixture is accessible in your test methods:

    $data  = $this->getFixture('myfixture');
    $name  = $data->name;
    $value = 'value';
    
    $this->assert($name === $value, 'The data is not valid.');

However, you can access the fixture data outside of a test method. You may need to do this in order to associate two fixtures:

    class SomeOtherFixture extends Testes\Fixture\FixtureAbstract
    {
        public static function generateData()
        {
            return [
                'id'   => 2,
                'link' => MyFixture::id(),
                'name' => 'value'
            ];
        }
    }

Asserting
---------

Your test classes come with a single assertion method aptly named `assert`.

    $this->assert($something, $message);

You may ask why there aren't more asserton methods. Take the following example:

    assert($value === true, 'message');
    assertEquals($value, true, 'message');

The former assertion is actually more readable and even shorter than the latter.

Running Tests
-------------

We use a finder to find our tests:
    
    $finder = new Testes\Finder\Finder('/path/to/tests');

We then use the finder to run the tests which returns the test suite that was run:

    $suite = $finder->run();

We can use the suite to get information about the test:

    echo sprintf(
        'Tests were completed in "%d" milliseconds and used "%s" bytes of memory.',
        $suite->getTime(),
        $suite->getMemory()
    );

If you want to run only a specific subset of tests, you pass a namespace to the finder:

    $finder = new Testes\Finder\Finder('/path/to/tests', 'Test/Subfolder');

Analyzing Coverage
------------------

Coverage analasys can be done using by running the tests after starting coverage analysis.

    $coverage = new Testes\Coverage\Coverage;
    $coverage->start();
    
    // run tests
    ...
    
    $analyzer = $coverage->stop();

You need to tell the analyzer what files to base the coverage on. One way is to add a directory of files:

    $analyzer->addDirectory('/path/to/code/needing/coverage');

You may also add specific files:

    $analyzer->addFile('/path/to/single/file/to/analyze.php');

Once you've added some files, you can filter them if you need to:

    $analyzer->is('.php$');

The analyzer gives us quite a bit of information such as which lines were not tested:

    // percent tested
    echo $analyzer->getPercentTested(2) . PHP_EOL . PHP_EOL;

    // untested files are files that are not 100% tested
    foreach ($analyzer->getUntestedFiles() as $file) {
        foreach ($analyzer->getUntestedLines() as $line) {
            echo $line . PHP_EOL;
        }
    }

Rendering Output
----------------

A lot of times you will need a the results in a format that something like a build server can consume. For this, we provide the JUnit format.

    $renderer = new Testes\Renderer\Junit;
    $rendered = $renderer->render($suite);ch
    
    file_put_contents('junit.xml', $rendered);

License
-------

Copyright (c) 2005-2013 Trey Shugart

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
