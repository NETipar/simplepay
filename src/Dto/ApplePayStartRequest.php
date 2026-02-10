<?php

namespace Netipar\SimplePay\Dto;

use Netipar\SimplePay\Enums\Currency;

readonly class ApplePayStartRequest
{
    public function __construct(
        public Currency $currency,
        public float $total,
        public string $orderRef,
        public string $customerEmail,
        public string $url,
    ) {}

    public function toArray(): array
    {
        return [
            'salt' => bin2hex(random_bytes(16)),
            'merchant' => '',
            'orderRef' => $this->orderRef,
            'currency' => $this->currency->value,
            'total' => $this->total,
            'customerEmail' => $this->customerEmail,
            'url' => $this->url,
        ];
    }
}
