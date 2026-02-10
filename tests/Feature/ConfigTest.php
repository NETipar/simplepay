<?php

use Netipar\SimplePay\Enums\Currency;
use Netipar\SimplePay\Support\MerchantResolver;

it('loads config with merchant credentials', function () {
    expect(config('simplepay.merchants.HUF.merchant'))->toBe('TEST_HUF_MERCHANT');
    expect(config('simplepay.merchants.HUF.secret_key'))->toBe('TEST_HUF_SECRET_KEY');
    expect(config('simplepay.merchants.EUR.merchant'))->toBe('TEST_EUR_MERCHANT');
});

it('resolves merchant by currency', function () {
    $resolver = app(MerchantResolver::class);

    expect($resolver->getMerchant(Currency::HUF))->toBe('TEST_HUF_MERCHANT');
    expect($resolver->getMerchant(Currency::EUR))->toBe('TEST_EUR_MERCHANT');
    expect($resolver->getMerchant(Currency::USD))->toBe('TEST_USD_MERCHANT');
});

it('resolves secret key by currency', function () {
    $resolver = app(MerchantResolver::class);

    expect($resolver->getSecretKey(Currency::HUF))->toBe('TEST_HUF_SECRET_KEY');
    expect($resolver->getSecretKey(Currency::EUR))->toBe('TEST_EUR_SECRET_KEY');
});

it('returns full merchant config', function () {
    $resolver = app(MerchantResolver::class);

    $config = $resolver->resolve(Currency::HUF);

    expect($config)->toHaveKey('merchant');
    expect($config)->toHaveKey('secret_key');
    expect($config['merchant'])->toBe('TEST_HUF_MERCHANT');
});

it('throws for unconfigured currency', function () {
    config()->set('simplepay.merchants.HUF', []);

    $resolver = app(MerchantResolver::class);
    $resolver->resolve(Currency::HUF);
})->throws(InvalidArgumentException::class);

it('has sandbox mode enabled in tests', function () {
    expect(config('simplepay.sandbox'))->toBeTrue();
});

it('has default timeout configured', function () {
    expect(config('simplepay.timeout'))->toBe(600);
});
