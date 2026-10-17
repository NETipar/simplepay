<?php

declare(strict_types=1);

namespace Netipar\SimplePay\Dto;

use Netipar\SimplePay\Enums\Currency;
use Random\RandomException;

readonly class CardStorageDoRequest
{
    /**
     * @param  array<int, Item>|null  $items
     */
    public function __construct(
        public Currency $currency,
        public int $transactionId,
        public string $cardId,
        public string $orderRef,
        public float $total,
        public string $customerEmail,
        public string $url,
        public ?array $items = null,
    ) {}

    /**
     * @return array<string, mixed>
     *
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
            'cardId' => $this->cardId,
            'total' => $this->total,
            'customerEmail' => $this->customerEmail,
            'url' => $this->url,
            'items' => $this->items ? array_map(fn (Item $item) => $item->toArray(), $this->items) : null,
        ], fn (mixed $value): bool => $value !== null);
    }
}
