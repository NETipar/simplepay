<?php

declare(strict_types=1);

namespace Netipar\SimplePay\Dto;

readonly class PaymentResponse
{
    /**
     * @param  string|null  $timeout  Missing from Request to Pay start responses.
     */
    public function __construct(
        public int $transactionId,
        public string $paymentUrl,
        public ?string $timeout = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            transactionId: (int) $data['transactionId'],
            paymentUrl: $data['paymentUrl'],
            timeout: $data['timeout'] ?? null,
        );
    }
}
