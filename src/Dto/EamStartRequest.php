<?php

namespace Netipar\SimplePay\Dto;

use Netipar\SimplePay\Enums\Currency;
use Random\RandomException;

readonly class EamStartRequest
{
    /**
     * @param  array<int, Item>|null  $items
     */
    public function __construct(
        public Currency $currency,
        public float $total,
        public string $orderRef,
        public string $customerEmail,
        public string $url,
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
            'total' => $this->total,
            'customerEmail' => $this->customerEmail,
            'url' => $this->url,
            'items' => $this->items ? array_map(fn (Item $item) => $item->toArray(), $this->items) : null,
        ], fn ($value) => $value !== null);
    }
}
