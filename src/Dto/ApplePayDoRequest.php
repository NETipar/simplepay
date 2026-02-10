<?php

namespace Netipar\SimplePay\Dto;

use Netipar\SimplePay\Enums\Currency;

readonly class ApplePayDoRequest
{
    public function __construct(
        public Currency $currency,
        public int $transactionId,
        public string $orderRef,
        public string $token,
    ) {}

    public function toArray(): array
    {
        return [
            'salt' => bin2hex(random_bytes(16)),
            'merchant' => '',
            'orderRef' => $this->orderRef,
            'currency' => $this->currency->value,
            'transactionId' => $this->transactionId,
            'token' => $this->token,
        ];
    }
}
