<?php

namespace Netipar\SimplePay\Dto;

use Netipar\SimplePay\Enums\Currency;
use Random\RandomException;

readonly class RtpDoRequest
{
    /**
     * @param  array<int, RtpPayment>  $payments
     */
    public function __construct(
        public Currency $currency,
        public array $payments,
    ) {}

    /**
     * @throws RandomException
     */
    public function toArray(): array
    {
        return [
            'salt' => bin2hex(random_bytes(16)),
            'merchant' => '',
            'currency' => $this->currency->value,
            'payments' => array_map(fn (RtpPayment $payment) => $payment->toArray(), $this->payments),
        ];
    }
}
