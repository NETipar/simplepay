<?php

declare(strict_types=1);

namespace Netipar\SimplePay\Dto;

readonly class FinishResponse
{
    public function __construct(
        public int $transactionId,
        public float $total,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            transactionId: (int) $data['transactionId'],
            total: (float) $data['approveTotal'],
        );
    }
}
