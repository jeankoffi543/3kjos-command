<?php

namespace Kjos\Command\Tests;

use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    //
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        removeTestDirectory();
    }

    protected function getPackageProviders($app)
    {
        return [
            \Kjos\Command\CommandServiceProvider::class,
        ];
    }
}
