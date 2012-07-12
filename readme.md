Introduction
------------

Testes - pardon my French - is a dead-simple unit test framework written in PHP. Its goal is to facilitate a simple, fast and maintainable way of testing.

Setup
-----

To set up Testes, you need to register your test directory:

    <?php
    
    require '../lib/Testes/Autoloader/Autoloader.php';
    Testes\Autoloader\Autoloader::register('./tests');

The autoloader automatically takes care of autoloading library files and uses `spl_autoload_register` so it can play nice with others.

After that, you just need to setup your test hierarchy. For every directory of tests you are required to create a corresponding test suite (may be optional in the future).

- tests
    - Test.php
    - Test
        - SubTest.php
        - Suite
            - SubTest.php

Given the above directory structure, your classes would be named as follows:

tests/Test.php

    <?php
    
    use Testes\Suite\Suite;
    
    class Test extends Suite
    {
        
    }

tests/Test/SubTest.php

    <?php
    
    namespace Test;
    use Testes\Test\UnitAbstract;
    
    class SubTest extends UnitAbstract
    {
        
    }

tests/Test/Suite/SubTest.php

    <?php
    
    namespace Test\Suite;
    use Testes\Test\UnitAbstract;
    
    class SubTest extends UnitAbstract
    {
        
    }

Test Hooks
----------

You can define hooks that are called at certain times during the test.

### setUp

Called before the tests are run.

### tearDown

Called after the tests are run.

Suite Hooks
-----------

### setUp

Called before the suite is run.

### tearDown

Called after the suite is run.

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

Asserting
---------

Your test classes come with an assertion method for asserting a given expression:

### assert ( *bool* $expression [, *string* $message [, *int* $code ]] );

More aren't provided because most really aren't necessary. There may be some added in the future but we don't see much need to add bloat for something like:

    <?php
    
    assert($value === true, 'message');
    assertEquals($value, true, 'message');

The former is actually more readable and shorter!

Running Tests
-------------

We use a finder to find our tests:

    <?php
    
    use Testes\Finder\Finder;
    
    $finder = new Finder('path/to/tests', 'OptionalTestNamespace');

We then use the finder to run the tests which returns the test suite that was run:

    $suite = $finder->run();

We can use the suite to get information about the test:

    echo sprintf(
        'Tests were completed in "%d" milliseconds and used "%s" bytes of memory.',
        $suite->getTime(),
        $suite->getMemory()
    );

Analyzing Coverage
------------------

Coverage analasys can be done using by running the tests after starting coverage analysis.

    <?php
    
    use Testes\Coverage\Coverage;
    
    // begin coverage
    $coverage = (new Coverage)->start();
    
    // run tests
    ...
    
    // stop analysis after tests are run
    $analyzer = $coverage->stop();
    
    // only analyze code in the specified directory
    $analyzer->addDirectory('path/to/code/needing/coverage');
    
    // add a file to analyze
    $analyzer->addFile('path/to/single/file/to/analyze.php');
    
    // only cover php files
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

    <?php
    
    use Testes\Renderer\Junit;

    file_put_contents('junit.xml', (new Junit)->render($suite));
