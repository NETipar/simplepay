<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Netipar\SimplePay\SimplePay;
use Netipar\SimplePay\SimplePayServiceProvider;

it('resolves the SimplePay singleton', function () {
    expect(app(SimplePay::class))->toBeInstanceOf(SimplePay::class)
        ->and(app(SimplePay::class))->toBe(app(SimplePay::class));
});

it('registers the IPN and back routes', function () {
    expect(Route::has('simplepay.ipn'))->toBeTrue()
        ->and(Route::has('simplepay.back'))->toBeTrue();
});

it('publishes the config file under its own tag', function () {
    $paths = ServiceProvider::pathsToPublish(SimplePayServiceProvider::class, 'simplepay-config');

    expect(array_values($paths))->toBe([config_path('simplepay.php')])
        ->and(array_keys($paths)[0])->toBeFile();
});

it('publishes the logo assets under its own tag', function () {
    $paths = ServiceProvider::pathsToPublish(SimplePayServiceProvider::class, 'simplepay-assets');

    expect(array_values($paths))->toBe([public_path('vendor/simplepay')])
        ->and(array_keys($paths)[0].'/logos/simplepay_200x50.png')->toBeFile();
});

it('publishes every resource under the package tag', function () {
    $paths = ServiceProvider::pathsToPublish(SimplePayServiceProvider::class, 'simplepay');

    expect(array_values($paths))->toBe([
        config_path('simplepay.php'),
        public_path('vendor/simplepay'),
    ]);
});
