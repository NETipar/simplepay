<?php

namespace Netipar\SimplePay\Dto;

readonly class RefundResponse
{
    public function __construct(
        public int $transactionId,
        public float $remainingTotal,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            transactionId: (int) $data['transactionId'],
            remainingTotal: (float) $data['remainingTotal'],
        );
    }
}
