<?php

declare(strict_types=1);

namespace Netipar\SimplePay\Dto;

use Netipar\SimplePay\Enums\Currency;
use Netipar\SimplePay\Enums\TransactionType;
use Netipar\SimplePay\Support\Client;

readonly class AutoPaymentRequest
{
    /**
     * @param  BrowserData|null  $browserData  The customer's browser data, sent as the `browser` object for the issuer's 3DS check.
     * @param  array<int, Item>|null  $items
     * @param  string|null  $url  Where SimplePay sends the customer back after a 3DS challenge.
     * @param  string|null  $threeDSReqAuthMethod  How the customer is registered: 01 guest, 02 registered, 05 third party.
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
        public ?string $url = null,
        public ?string $threeDSReqAuthMethod = null,
        public ?string $language = null,
    ) {}

    /**
     * @return array<string, mixed>
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
            'cardData' => $this->cardData->toArray(),
            'type' => $this->type->value,
            'browser' => $this->browserData?->toArray(),
            'threeDSReqAuthMethod' => $this->threeDSReqAuthMethod,
            'url' => $this->url,
            'language' => $this->language,
            'customer' => $this->customer,
            'invoice' => $this->invoice?->toArray(),
            'delivery' => $this->delivery?->toArray(),
            'items' => $this->items ? array_map(fn (Item $item) => $item->toArray(), $this->items) : null,
            'sdkVersion' => Client::SDK_VERSION,
        ], fn (mixed $value): bool => $value !== null);
    }
}
