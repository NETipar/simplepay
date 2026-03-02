<?php

namespace Netipar\SimplePay;

use Illuminate\Support\ServiceProvider;
use Netipar\SimplePay\Contracts\BackUrlResponse;
use Netipar\SimplePay\Http\Responses\DefaultBackUrlResponse;
use Netipar\SimplePay\Support\Client;
use Netipar\SimplePay\Support\MerchantResolver;

class SimplePayServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/simplepay.php', 'simplepay');

        $this->app->singleton(MerchantResolver::class);
        $this->app->singleton(Client::class);
        $this->app->singleton(SimplePay::class);
        $this->app->singleton(BackUrlResponse::class, DefaultBackUrlResponse::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/simplepay.php' => config_path('simplepay.php'),
            ], 'simplepay-config');

            $this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/simplepay'),
            ], 'simplepay-assets');
        }

        $this->loadRoutesFrom(__DIR__.'/../routes/simplepay.php');
    }
}
