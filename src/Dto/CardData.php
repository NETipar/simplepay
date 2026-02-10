<?php

namespace Netipar\SimplePay\Dto;

readonly class CardData
{
    public function __construct(
        public string $cardNumber,
        public string $expiry,
        public string $cvc,
        public ?string $cardHolder = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'number' => $this->cardNumber,
            'expiry' => $this->expiry,
            'cvc' => $this->cvc,
            'holder' => $this->cardHolder,
        ], fn ($value) => $value !== null);
    }
}
