<?php

declare(strict_types=1);

namespace Netipar\SimplePay\Dto;

use Netipar\SimplePay\Enums\Currency;

readonly class GooglePayResponse
{
    public function __construct(
        public int $transactionId,
        public string $orderRef,
        public ?Currency $currency = null,
        public ?float $total = null,
        public ?string $redirectUrl = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            transactionId: (int) $data['transactionId'],
            orderRef: (string) $data['orderRef'],
            currency: isset($data['currency']) ? Currency::from($data['currency']) : null,
            total: isset($data['total']) ? (float) $data['total'] : null,
            redirectUrl: isset($data['redirectUrl']) ? (string) $data['redirectUrl'] : null,
        );
    }

    /**
     * Whether the card issuer asked for a 3DS challenge: send the customer to $redirectUrl.
     */
    public function requiresChallenge(): bool
    {
        return $this->redirectUrl !== null;
    }
}
