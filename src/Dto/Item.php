<?php

namespace Netipar\SimplePay\Dto;

readonly class Item
{
    public function __construct(
        public string $title,
        public float $price,
        public int $quantity = 1,
        public ?string $ref = null,
        public ?string $description = null,
        public ?float $tax = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'ref' => $this->ref,
            'title' => $this->title,
            'description' => $this->description,
            'amount' => $this->quantity,
            'price' => $this->price,
            'tax' => $this->tax,
        ], fn ($value) => $value !== null);
    }
}
