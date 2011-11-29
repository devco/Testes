Introduction
------------

Testes - pardon my French - is a dead-simple unit test framework written in PHP. Its goal is to facilitate simple, fast and maintainable way of testing.

Setup
-----

To set up Testes, you need to register your test directory.

    <?php
    
    require '../lib/Testes/Autoloader.php';
    \Testes\Autoloader::register('/path/to/test/directory');

After that, you just need to setup your test hierarchy. For every directory of tests, Testes requires that you create a corresponding test suite, even if it is empty. This may be optional in the future.

- tests
    - Test.php
    - Test
        - SubTest.php
        - Suite.php
        - Suite
            - SubTest.php

Given the above directory structure, your classes would be named as follows:

tests/Test.php

    <?php
    
    class Test extends \Testes\Test
    {
        
    }

tests/Test/SubTest.php

    <?php
    
    namespace Test;
    
    class SubTest extends \Testes\Test
    {
        
    }

tests/Test/Suite.php

    <?php
    
    namespace Test;
    
    class Suite extends \Testes\Suite
    {
        
    }

tests/Test/Suite/SubTest.php

    <?php
    
    namespace Test\Suite;
    
    class SubTest extends \Testes\Test
    {
        
    }

Running Tests and Suites
------------------------

Running all tests is simple. All you do is instantiate the root test suite and call `run()` on it:

    $test = new Test;
    $test->run();

Rendering Test Data
-------------------

You're also going to need to output some data. Testes comes with a few different adapters at your disposal:

* Cli
* Html
* Junit

Rendering is also quite simple:

    $renderer = new \Testes\Output\Junit;
    echo $renderer->render($test);

And the output would be automated because `Testes_UnitTest` extends `Testes_UnitTest_Suite`, but provides a generic way of outputting test results using `__toString()`.

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

Test methods are any public methods that aren't in any parent classes or implemented interfaces. This means that public methods such as `setUp()` and `tearDown()` aren't valid test methods and won't be run as part of testing.

Some good test names:

- makeSureAllPropertiesAreSetWhenHydrating()
- testAssertionAggregation()
- completeRequestDispatchingTest()

You cannot define test methods as part of a test suite. They must exist in a test class.

Asserting
---------

Assertion methods are just wrappers for instantiating assertion objects. These classes are:

- Testes_UnitTest_Assertion : Testes_Exception
- Testes_UnitTest_FatalAssertion : Testes_UnitTest_Assertion

Your test class comes with a single assertion method along with a method for triggering a fatal assertion:

### \Testes\Test->assert ( *bool* $expression [, *string* $message ] );

Asserts a failure if `$expression` evaluates to `false`.

### \Testes\Test->assertFatal ( *bool* $expression [, *string* $message ] );

Asserts a failure if `$expression` evaluates to `false` and halts test execution. Tear down hooks are still executed.

Why isn't there 1,000,000 different assertion methods to choose from?
---------------------------------------------------------------------

Because there isn't much point in bloating an API and writing a heap of different methods that - in the end - just do the same thing.

Customizing Output
------------------

If you need to customize your output, you can use the built-in API as you see fit, or you can implement a renderer:

    