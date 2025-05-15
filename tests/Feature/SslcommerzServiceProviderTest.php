<?php

declare(strict_types=1);

use Raziul\Sslcommerz\Exceptions\SslcommerzException;
use Raziul\Sslcommerz\SslcommerzClient;
use Raziul\Sslcommerz\SslcommerzServiceProvider;
use Spatie\LaravelPackageTools\Package;

describe('SslcommerzServiceProvider', function () {
    beforeEach(function () {
        $this->app = $this->refreshApplication();
        $this->serviceProvider = new SslcommerzServiceProvider($this->app);
    });

    it('registers package configuration', function () {
        $package = Mockery::mock(Package::class);
        $package->shouldReceive('name')->with('sslcommerz-laravel')->once()->andReturnSelf();
        $package->shouldReceive('hasConfigFile')->with('sslcommerz')->once()->andReturnSelf();
        $package->shouldReceive('hasInstallCommand')->once()->andReturnSelf();

        $this->serviceProvider->configurePackage($package);
    });

    it('throws exception for missing store credentials', function () {
        config()->set('sslcommerz', [
            'store' => [
                'id' => '',
                'password' => null,
                'currency' => 'BDT',
            ],
        ]);

        app(SslcommerzClient::class);
    })->throws(SslcommerzException::class, 'SSLCommerz store credentials are not set.');
});
