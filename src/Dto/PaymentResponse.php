<?php

declare(strict_types=1);

namespace Netipar\SimplePay\Dto;

readonly class PaymentResponse
{
    public function __construct(
        public int $transactionId,
        public string $paymentUrl,
        public string $timeout,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            transactionId: $data['transactionId'],
            paymentUrl: $data['paymentUrl'],
            timeout: $data['timeout'],
        );
    }
}
