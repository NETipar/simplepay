<?php

namespace Netipar\SimplePay\Tests;

use Netipar\SimplePay\SimplePayServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            SimplePayServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('simplepay.merchants', [
            'HUF' => [
                'merchant' => 'TEST_HUF_MERCHANT',
                'secret_key' => 'TEST_HUF_SECRET_KEY',
            ],
            'EUR' => [
                'merchant' => 'TEST_EUR_MERCHANT',
                'secret_key' => 'TEST_EUR_SECRET_KEY',
            ],
            'USD' => [
                'merchant' => 'TEST_USD_MERCHANT',
                'secret_key' => 'TEST_USD_SECRET_KEY',
            ],
        ]);

        $app['config']->set('simplepay.sandbox', true);
        $app['config']->set('simplepay.urls.back', 'https://example.com/payment/back');
        $app['config']->set('simplepay.timeout', 600);
    }
}
