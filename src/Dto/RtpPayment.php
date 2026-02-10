<?php

namespace Netipar\SimplePay\Dto;

readonly class RtpPayment
{
    public function __construct(
        public string $orderRef,
        public float $total,
        public string $customerEmail,
        public string $customer,
    ) {}

    public function toArray(): array
    {
        return [
            'orderRef' => $this->orderRef,
            'total' => $this->total,
            'customerEmail' => $this->customerEmail,
            'customer' => $this->customer,
        ];
    }
}
