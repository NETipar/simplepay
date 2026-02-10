<?php

use Netipar\SimplePay\Enums\Currency;
use Netipar\SimplePay\Support\MerchantResolver;

it('resolves merchant and secret key for a valid currency', function () {
    $resolver = new MerchantResolver;

    $result = $resolver->resolve(Currency::HUF);

    expect($result)->toBeArray()
        ->and($result['merchant'])->toBe('TEST_HUF_MERCHANT')
        ->and($result['secret_key'])->toBe('TEST_HUF_SECRET_KEY');
});

it('returns merchant string for a valid currency', function () {
    $resolver = new MerchantResolver;

    expect($resolver->getMerchant(Currency::EUR))->toBe('TEST_EUR_MERCHANT');
});

it('returns secret key for a valid currency', function () {
    $resolver = new MerchantResolver;

    expect($resolver->getSecretKey(Currency::USD))->toBe('TEST_USD_SECRET_KEY');
});

it('throws exception for unconfigured currency', function () {
    config()->set('simplepay.merchants.HUF', []);

    $resolver = new MerchantResolver;

    $resolver->resolve(Currency::HUF);
})->throws(InvalidArgumentException::class, 'SimplePay merchant not configured for currency: HUF');
