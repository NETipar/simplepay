<?php

declare(strict_types=1);

namespace Netipar\SimplePay;

use Illuminate\Support\ServiceProvider;
use Netipar\SimplePay\Contracts\BackUrlResponse;
use Netipar\SimplePay\Http\Responses\DefaultBackUrlResponse;
use Netipar\SimplePay\Support\Client;
use Netipar\SimplePay\Support\MerchantResolver;

class SimplePayServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/simplepay.php', 'simplepay');

        $this->app->singleton(MerchantResolver::class);
        $this->app->singleton(Client::class);
        $this->app->singleton(SimplePay::class);
        $this->app->singleton(BackUrlResponse::class, DefaultBackUrlResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/simplepay.php');

        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/simplepay.php' => config_path('simplepay.php'),
        ], ['simplepay', 'simplepay-config']);

        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/simplepay'),
        ], ['simplepay', 'simplepay-assets']);
    }
}
