<?php

namespace Netipar\SimplePay\Dto;

readonly class Address
{
    public function __construct(
        public string $name,
        public string $country,
        public string $city,
        public string $zip,
        public string $address,
        public ?string $address2 = null,
        public ?string $state = null,
        public ?string $company = null,
        public ?string $phone = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'company' => $this->company,
            'country' => $this->country,
            'state' => $this->state,
            'city' => $this->city,
            'zip' => $this->zip,
            'address' => $this->address,
            'address2' => $this->address2,
            'phone' => $this->phone,
        ], fn ($value) => $value !== null);
    }
}
