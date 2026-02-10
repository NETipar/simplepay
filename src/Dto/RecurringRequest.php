<?php

namespace Netipar\SimplePay\Dto;

use Netipar\SimplePay\Enums\Currency;
use Netipar\SimplePay\Enums\TransactionType;
use Random\RandomException;

readonly class RecurringRequest
{
    /**
     * @param  array<int, Item>|null  $items
     */
    public function __construct(
        public Currency $currency,
        public int $transactionId,
        public string $orderRef,
        public float $total,
        public string $customerEmail,
        public string $url,
        public TransactionType $type,
        public ?array $items = null,
    ) {}

    /**
     * @throws RandomException
     */
    public function toArray(): array
    {
        return array_filter([
            'salt' => bin2hex(random_bytes(16)),
            'merchant' => '',
            'orderRef' => $this->orderRef,
            'currency' => $this->currency->value,
            'transactionId' => $this->transactionId,
            'total' => $this->total,
            'customerEmail' => $this->customerEmail,
            'url' => $this->url,
            'type' => $this->type->value,
            'items' => $this->items ? array_map(fn (Item $item) => $item->toArray(), $this->items) : null,
        ], fn ($value) => $value !== null);
    }
}
