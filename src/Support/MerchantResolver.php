<?php

namespace Netipar\SimplePay\Support;

use InvalidArgumentException;
use Netipar\SimplePay\Enums\Currency;

class MerchantResolver
{
    /**
     * @return array{merchant: string, secret_key: string}
     */
    public function resolve(Currency $currency): array
    {
        $merchants = config('simplepay.merchants');
        $key = $currency->value;

        if (! isset($merchants[$key]['merchant'], $merchants[$key]['secret_key'])) {
            throw new InvalidArgumentException("SimplePay merchant not configured for currency: {$key}");
        }

        return $merchants[$key];
    }

    public function getMerchant(Currency $currency): string
    {
        return $this->resolve($currency)['merchant'];
    }

    public function getSecretKey(Currency $currency): string
    {
        return $this->resolve($currency)['secret_key'];
    }

    public function getSecretKeyByMerchant(string $merchant): string
    {
        $merchants = config('simplepay.merchants');

        foreach ($merchants as $config) {
            if (isset($config['merchant'], $config['secret_key']) && $config['merchant'] === $merchant) {
                return $config['secret_key'];
            }
        }

        throw new InvalidArgumentException("SimplePay merchant not configured: {$merchant}");
    }
}
