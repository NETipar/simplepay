<?php

namespace Netipar\SimplePay\Dto;

use Netipar\SimplePay\Enums\Currency;
use Netipar\SimplePay\Enums\PaymentStatus;

readonly class IpnMessage
{
    public function __construct(
        public string $orderRef,
        public int $transactionId,
        public PaymentStatus $status,
        public Currency $currency,
        public float $total,
        public ?string $finishDate = null,
        public ?string $paymentMethod = null,
        public ?string $cardMask = null,
        public ?string $merchant = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            orderRef: $data['orderRef'],
            transactionId: (int) $data['transactionId'],
            status: PaymentStatus::from($data['status']),
            currency: Currency::from($data['currency']),
            total: (float) $data['total'],
            finishDate: $data['finishDate'] ?? null,
            paymentMethod: $data['paymentMethod'] ?? null,
            cardMask: $data['cardMask'] ?? null,
            merchant: $data['merchant'] ?? null,
        );
    }
}
