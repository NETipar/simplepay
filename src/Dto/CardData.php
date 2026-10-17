<?php

declare(strict_types=1);

namespace Netipar\SimplePay\Dto;

readonly class CardData
{
    public function __construct(
        public string $cardNumber,
        public string $expiry,
        public string $cvc,
        public ?string $cardHolder = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'number' => $this->cardNumber,
            'expiry' => $this->expiry,
            'cvc' => $this->cvc,
            'holder' => $this->cardHolder,
        ], fn (mixed $value): bool => $value !== null);
    }
}
