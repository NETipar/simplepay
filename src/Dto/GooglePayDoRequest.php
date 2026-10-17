<?php

declare(strict_types=1);

namespace Netipar\SimplePay\Dto;

use Netipar\SimplePay\Enums\Currency;
use Netipar\SimplePay\Enums\TransactionType;
use Random\RandomException;

readonly class GooglePayDoRequest
{
    /**
     * @param  string  $googlePayToken  The Base64 encoded `paymentMethodData.tokenizationData.token` from Google Pay.
     * @param  string  $url  Where SimplePay sends the customer back after a 3DS challenge.
     * @param  string|null  $threeDSReqAuthMethod  How the customer is registered: 01 guest, 02 registered, 05 third party.
     */
    public function __construct(
        public Currency $currency,
        public float $total,
        public string $orderRef,
        public string $customerEmail,
        public string $googlePayToken,
        public string $url,
        public string $language = 'HU',
        public TransactionType $type = TransactionType::Cit,
        public ?string $threeDSReqAuthMethod = null,
        public ?Address $invoice = null,
        public ?string $customer = null,
        public ?int $timeout = null,
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
            'currency' => $this->currency->value,
            'total' => $this->total,
            'customerEmail' => $this->customerEmail,
            'language' => $this->language,
            'googlePayToken' => $this->googlePayToken,
            'type' => $this->type->value,
            'threeDSReqAuthMethod' => $this->threeDSReqAuthMethod,
            'invoice' => $this->invoice?->toArray(),
            'customer' => $this->customer,
            'url' => $this->url,
            'timeout' => date('c', time() + ($this->timeout ?? (int) config('simplepay.timeout', 600))),
            'sdkVersion' => 'SimplePay_PHP_SDK_2.1_Laravel',
        ], fn (mixed $value): bool => $value !== null);
    }
}
