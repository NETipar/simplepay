<?php

namespace Netipar\SimplePay\Dto;

readonly class FinishResponse
{
    public function __construct(
        public int $transactionId,
        public float $total,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            transactionId: (int) $data['transactionId'],
            total: (float) $data['approveTotal'],
        );
    }
}
