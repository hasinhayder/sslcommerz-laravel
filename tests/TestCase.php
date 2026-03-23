<?php

declare(strict_types=1);

namespace HasinHayder\Sslcommerz\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use HasinHayder\Sslcommerz\SslcommerzServiceProvider;

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
