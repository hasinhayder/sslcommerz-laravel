<?php

declare(strict_types=1);

namespace HasinHayder\Sslcommerz\Tests;

use HasinHayder\Sslcommerz\SslcommerzServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra {
    protected function setUp(): void {
        parent::setUp();
    }

    protected function getPackageProviders($app) {
        return [
            SslcommerzServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app) {
        config()->set('database.default', 'testing');
    }
}
