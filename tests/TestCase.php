<?php

namespace Kjos\Command\Tests;

use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    //
    protected function setUp(): void
    {
        parent::setUp();
        kjos_create_test_directory();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        kjos_remove_test_directory();
    }

    protected function getPackageProviders($app)
    {
        return [
            \Kjos\Command\CommandServiceProvider::class,
        ];
    }
}
