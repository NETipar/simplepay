<?php

declare(strict_types=1);

namespace Netipar\SimplePay\Dto;

use Netipar\SimplePay\Enums\Currency;
use Netipar\SimplePay\Enums\TransactionType;
use Random\RandomException;

/**
 * OneClick payment with a stored card (`do` endpoint).
 */
readonly class CardStorageDoRequest
{
    /**
     * @param  int|null  $transactionId  Deprecated: the `do` endpoint identifies the card by $cardId; not sent, removed in the next major version.
     * @param  array<int, Item>|null  $items
     * @param  string|null  $cardSecret  The secret the customer gave when the card was stored.
     * @param  string|null  $threeDSReqAuthMethod  How the customer is registered: 01 guest, 02 registered, 05 third party.
     * @param  BrowserData|null  $browser  The customer's browser data for the issuer's 3DS check.
     */
    public function __construct(
        public Currency $currency,
        public ?int $transactionId,
        public string $cardId,
        public string $orderRef,
        public float $total,
        public string $customerEmail,
        public string $url,
        public ?array $items = null,
        public ?string $cardSecret = null,
        public TransactionType $type = TransactionType::Cit,
        public ?string $threeDSReqAuthMethod = null,
        public ?BrowserData $browser = null,
        public ?string $customer = null,
        public ?Address $invoice = null,
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
            'methods' => ['CARD'],
            'currency' => $this->currency->value,
            'cardId' => $this->cardId,
            'cardSecret' => $this->cardSecret,
            'total' => $this->total,
            'customerEmail' => $this->customerEmail,
            'customer' => $this->customer,
            'type' => $this->type->value,
            'threeDSReqAuthMethod' => $this->threeDSReqAuthMethod,
            'browser' => $this->browser?->toArray(),
            'invoice' => $this->invoice?->toArray(),
            'url' => $this->url,
            'items' => $this->items ? array_map(fn (Item $item) => $item->toArray(), $this->items) : null,
        ], fn (mixed $value): bool => $value !== null);
    }
}
