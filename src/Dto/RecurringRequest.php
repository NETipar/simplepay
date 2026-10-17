<?php

declare(strict_types=1);

namespace Netipar\SimplePay\Dto;

use InvalidArgumentException;
use Netipar\SimplePay\Enums\Currency;
use Netipar\SimplePay\Enums\TransactionType;
use Random\RandomException;

/**
 * Recurring payment with a token (`dorecurring` endpoint).
 */
readonly class RecurringRequest
{
    /**
     * @param  int|null  $transactionId  Deprecated: the `dorecurring` endpoint identifies the card by $token; not sent, removed in the next major version.
     * @param  array<int, Item>|null  $items
     * @param  string|null  $token  The recurring token from the card registration. Required.
     * @param  string|null  $threeDSReqAuthMethod  How the customer is registered: 01 guest, 02 registered, 05 third party.
     */
    public function __construct(
        public Currency $currency,
        public ?int $transactionId,
        public string $orderRef,
        public float $total,
        public string $customerEmail,
        public string $url,
        public TransactionType $type,
        public ?array $items = null,
        public ?string $token = null,
        public ?string $threeDSReqAuthMethod = null,
        public ?string $customer = null,
    ) {}

    /**
     * @return array<string, mixed>
     *
     * @throws RandomException
     * @throws InvalidArgumentException When the recurring token is missing.
     */
    public function toArray(): array
    {
        if ($this->token === null || $this->token === '') {
            throw new InvalidArgumentException('A recurring payment needs the recurring token: pass `token` to RecurringRequest.');
        }

        return array_filter([
            'salt' => bin2hex(random_bytes(16)),
            'merchant' => '',
            'orderRef' => $this->orderRef,
            'methods' => ['CARD'],
            'currency' => $this->currency->value,
            'token' => $this->token,
            'total' => $this->total,
            'customerEmail' => $this->customerEmail,
            'customer' => $this->customer,
            'url' => $this->url,
            'type' => $this->type->value,
            'threeDSReqAuthMethod' => $this->threeDSReqAuthMethod,
            'items' => $this->items ? array_map(fn (Item $item) => $item->toArray(), $this->items) : null,
        ], fn (mixed $value): bool => $value !== null);
    }
}
