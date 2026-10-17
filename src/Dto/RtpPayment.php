<?php

declare(strict_types=1);

namespace Netipar\SimplePay\Dto;

/**
 * One payment request of a Request to Pay batch (`rtp/do`).
 */
readonly class RtpPayment
{
    /**
     * @param  string|null  $deadline  Payment deadline (ISO 8601); defaults to 30 days from now.
     * @param  string|null  $additionalInfo  The announcement shown with the bank transfer.
     * @param  string|null  $customerBankAccount  The customer's bank account number, when known.
     */
    public function __construct(
        public string $orderRef,
        public float $total,
        public string $customerEmail,
        public string $customer,
        public ?string $deadline = null,
        public ?string $additionalInfo = null,
        public ?string $customerBankAccount = null,
        public string $language = 'HU',
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'orderRef' => $this->orderRef,
            'total' => $this->total,
            'customerEmail' => $this->customerEmail,
            'customer' => $this->customer,
            'deadline' => $this->deadline ?? date('c', time() + RtpDoRequest::DEFAULT_DEADLINE),
            'additionalInfo' => $this->additionalInfo,
            'customerBankAccount' => $this->customerBankAccount,
            'language' => $this->language,
        ], fn (mixed $value): bool => $value !== null);
    }
}
