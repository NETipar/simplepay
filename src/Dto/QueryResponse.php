<?php

namespace Netipar\SimplePay\Dto;

use Netipar\SimplePay\Enums\Currency;
use Netipar\SimplePay\Enums\PaymentStatus;
use RuntimeException;

readonly class QueryResponse
{
    public function __construct(
        public int $transactionId,
        public string $orderRef,
        public PaymentStatus $status,
        public float $total,
        public Currency $currency,
        public string $merchant,
        public ?float $remainingTotal = null,
        public ?string $paymentDate = null,
        public ?string $finishDate = null,
        public ?string $method = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $transaction = $data['transactions'][0] ?? null;

        if ($transaction === null) {
            throw new RuntimeException('No transactions found in query response');
        }

        return new self(
            transactionId: (int) $transaction['transactionId'],
            orderRef: $transaction['orderRef'],
            status: PaymentStatus::from($transaction['status']),
            total: (float) $transaction['total'],
            currency: Currency::from($transaction['currency'] ?? $data['currency'] ?? 'HUF'),
            merchant: $transaction['merchant'] ?? $data['merchant'],
            remainingTotal: isset($transaction['remainingTotal']) ? (float) $transaction['remainingTotal'] : null,
            paymentDate: $transaction['paymentDate'] ?? null,
            finishDate: $transaction['finishDate'] ?? null,
            method: $transaction['method'] ?? null,
        );
    }
}
