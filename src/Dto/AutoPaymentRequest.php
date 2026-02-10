<?php

namespace Netipar\SimplePay\Dto;

use Netipar\SimplePay\Enums\Currency;
use Netipar\SimplePay\Enums\TransactionType;

readonly class AutoPaymentRequest
{
    /**
     * @param  array<int, Item>|null  $items
     */
    public function __construct(
        public Currency $currency,
        public float $total,
        public string $orderRef,
        public string $customerEmail,
        public CardData $cardData,
        public TransactionType $type,
        public ?BrowserData $browserData = null,
        public ?string $customer = null,
        public ?Address $invoice = null,
        public ?Address $delivery = null,
        public ?array $items = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'salt' => bin2hex(random_bytes(16)),
            'merchant' => '',
            'orderRef' => $this->orderRef,
            'currency' => $this->currency->value,
            'total' => $this->total,
            'customerEmail' => $this->customerEmail,
            'cardData' => $this->cardData->toArray(),
            'type' => $this->type->value,
            'browserData' => $this->browserData?->toArray(),
            'customer' => $this->customer,
            'invoice' => $this->invoice?->toArray(),
            'delivery' => $this->delivery?->toArray(),
            'items' => $this->items ? array_map(fn (Item $item) => $item->toArray(), $this->items) : null,
            'sdkVersion' => 'netipar-simplepay:laravel',
        ], fn ($value) => $value !== null);
    }
}
