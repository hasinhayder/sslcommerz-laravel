<?php

declare(strict_types=1);

namespace Raziul\Sslcommerz\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Raziul\Sslcommerz\SslcommerzServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            SslcommerzServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
    }
}
