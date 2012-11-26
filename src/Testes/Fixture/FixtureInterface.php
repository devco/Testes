<?php

namespace Testes\Fixture;

interface FixtureInterface
{
    /**
     * Sets up the fixture.
     * 
     * @return void
     */
    public function setUp();

    /**
     * Tears down the fixture.
     * 
     * @return void
     */
    public function tearDown();

    /**
     * Returns the fixture data array.
     * 
     * @return array
     */
    public static function data();
}