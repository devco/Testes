Introduction
------------

Testes - >:D - Is a dead-simple unit test framework written in PHP. It's goal is to facilitate simple, fast and maintainable way of testing.

What, no code coverage?
-----------------------

Yes. You heard that correctly - code coverage analasys is useless. Whats the point of unit testing 500 methods, having complete coverage and your system still doesn't work? Unless you enjoy punishing yourself, nothing! You'll have to change 200 of those when you update your API anyways. Eventually, the mislead developers will just stop writing tests. BDD, don't TDD.

Setup
-----

To set up Testes, you need to register your test directory.

    <?php
    
    require '../lib/Testes/Autoloader.php';
    Testes_Autoloader::register('./tests');

After that, you just need to setup your test heirarchy.

- tests
    - Test.php
    - Test
        - SubTest.php
        - Suite.php
        - Suite
            - SubTest.php

Your classes would be named as follows:

tests/Test.php

    <?php
    
    class Test extends Testes_UnitTest
    {
    
    }

tests/Test/SubTest.php

    <?php
    
    class Test_SubTest extends Testes_UnitTest_Test
    {
    
    }

tests/Test/Suite.php

    <?php
    
    class Test_Suite extends Testes_UnitTest_Suite
    {
    
    }

tests/Test/Suite/SubTest.php

    <?php
    
    class Test_Suite_SubTest extends Testes_UnitTest_Test
    {
    
    }

Running Tests and Suites
------------------------

You can run all tests:

    $test = new Test;
    echo $test->run();

And the output would be automated because `Testes_UnitTest` extends `Testes_UnitTest_Suite`, but provides a generic way of outputting test results using `__toString()`.

Or you can run suites/tests individually:

    // running a single test
    // would run all tests in Test/SubTest.php
    $test = new Test_SubTest;
    $test->run();
    
    // aggregates all assertions in the test
    foreach ($test->assertions() as $assertion) {
        ...
    }
    
    // running a test suite
    // would run all tests and suites recursively under Test/Suite
    $suite = new Test_Suite;
    $suite->run();
    
    // recursively aggregates all assertions
    foreach ($suite->assertions() as $assertion) {
        ...
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

Your test class comes with 1 assertion method for each assertion object.

### Testes_UnitTest_Test->assert ( *bool* $expression [, *string* $message ] );

Asserts a failure if `$expression` evaluates to `false`.

### Testes_UnitTest_Test->assertFatal ( *bool* $expression [, *string* $message ] );

Asserts a failure if `$expression` evaluates to `false` and halts test execution. Tear down hooks are still executed.

Why isn't there 1,000,000 different assertion methods to choose from?
---------------------------------------------------------------------

Because there isn't much point in bloating an API and writing a heap of different methods that - in the end - just do the same thing and require the same amount of work.

Customizing Output
------------------

Since assertions are objects and derive from exceptions, you have access to a number of methods to gather information about them. On top of the ones inherited from the base Exception class, these are:

### getTestFile ( *void* )

Returns the file the assertion was in.

### getTestLine ( *void* )

Returns the line number of the assertion.

### getTestClass ( *void* )

Returns the class name of the method in which the assertion was made.

### getTestMethod ( *void* )

Returns the method name of the method in which the assertion was made.

The `Testes_Output` class provides helper methods for automating output depending on the execution environment.

To generate some output, we may consider the following:

    if ($assertions = $test->assertions()) {
        foreach ($assertions() as $assertion) {
            echo $assertion->getTestClass()
               . '->'
               . $assertion->getTestMethod()
               . '() in '
               . $assertion->getTestFile()
               . ' on '
               . $assertion->getTestLine()
               . ': ' 
               . $assertion->getMessage()
               . Testes_Output::breaker();
        }
    } else {
        echo 'All tests passed!' . Testes_Output::breaker();
    }

Benchmarking
------------

There are also benchmarking capabilities built in. Although rudimentary, they provide a simple way to test simple performance metrics. Benchmarks are setup and run in EXACTLY the same way as unit tests, except where you extend *_UnitTest_* classes, you'll be extending *_Benchmark_* classes. The only other difference is that there is no assertions.

Customizing Benchmark Output
----------------------------

When generating output, you can retrieve benchmark metrics using the `results()` method on both `Testes_Benchmark_Test` and `Testes_Benchmark_Suite` classes.

We may consider the following:

    $br  = Testes_Output::breaker();
    $sp1 = Testes_Output::spacer(1);
    $sp2 = Testes_Output::spacer(2);
    $sp3 = Testes_Output::spacer(3);
    $sp4 = Testes_Output::spacer(4);

    if (!Testes_Output::isCli()) {
        echo '<pre>';
    }

    foreach ($this->results() as $suite => $benchmarks) {
    	echo $suite . $br;
    	foreach ($benchmarks as $benchmark => $result) {
    		echo $sp2 . $benchmark . $br
    		   . $sp4 . 'memory' . $sp1 . ':' . $sp1 . round($result['memory'] / 1024 / 1024, 3) . ' MB' . $br
    		   . $sp4 . 'time' . $sp3 . ':' . $sp1 . round($result['time'], 3) . ' seconds' . $br;
    	}
    }

    if (!Testes_Output::isCli()) {
        echo '</pre>';
    }

    echo Testes_Output::breaker();