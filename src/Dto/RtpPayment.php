<?php

declare(strict_types=1);

namespace Netipar\SimplePay\Dto;

readonly class RtpPayment
{
    public function __construct(
        public string $orderRef,
        public float $total,
        public string $customerEmail,
        public string $customer,
    ) {}

    /**
     * @return array<string, mixed>
     */
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
