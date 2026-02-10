<?php

namespace Netipar\SimplePay\Dto;

use Netipar\SimplePay\Enums\Currency;
use Random\RandomException;

readonly class FinishRequest
{
    public function __construct(
        public Currency $currency,
        public int $transactionId,
        public string $orderRef,
        public float $amount,
        public float $originalTotal,
        public ?float $approveTotal = null,
    ) {}

    /**
     * @throws RandomException
     */
    public function toArray(): array
    {
        $data = [
            'salt' => bin2hex(random_bytes(16)),
            'merchant' => '',
            'orderRef' => $this->orderRef,
            'transactionId' => $this->transactionId,
            'currency' => $this->currency->value,
            'originalTotal' => $this->originalTotal,
            'approveTotal' => $this->approveTotal ?? $this->amount,
            'sdkVersion' => 'SimplePay_PHP_SDK_2.1_Laravel',
        ];

        return $data;
    }
}
