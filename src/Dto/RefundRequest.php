<?php

namespace Netipar\SimplePay\Dto;

use Netipar\SimplePay\Enums\Currency;
use Random\RandomException;

readonly class RefundRequest
{
    public function __construct(
        public Currency $currency,
        public int $transactionId,
        public string $orderRef,
        public float $amount,
    ) {}

    /**
     * @throws RandomException
     */
    public function toArray(): array
    {
        return [
            'salt' => bin2hex(random_bytes(16)),
            'merchant' => '',
            'orderRef' => $this->orderRef,
            'transactionId' => $this->transactionId,
            'currency' => $this->currency->value,
            'refundTotal' => $this->amount,
            'sdkVersion' => 'SimplePay_PHP_SDK_2.1_Laravel',
        ];
    }
}
