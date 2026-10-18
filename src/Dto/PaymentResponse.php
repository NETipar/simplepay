<?php

declare(strict_types=1);

namespace Netipar\SimplePay\Dto;

readonly class PaymentResponse
{
    /**
     * @param  string|null  $timeout  Missing from Request to Pay start responses.
     * @param  list<string>  $tokens  The recurring tokens of a card registration; active after the successful payment.
     */
    public function __construct(
        public int $transactionId,
        public string $paymentUrl,
        public ?string $timeout = null,
        public array $tokens = [],
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
            tokens: array_values(array_map(strval(...), (array) ($data['tokens'] ?? []))),
        );
    }
}
