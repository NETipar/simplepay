<?php

namespace Netipar\SimplePay\Dto;

use Netipar\SimplePay\Enums\Currency;
use Random\RandomException;

readonly class RtpStartRequest
{
    /**
     * @param  array<int, Item>|null  $items
     */
    public function __construct(
        public Currency $currency,
        public float $total,
        public string $orderRef,
        public string $customerEmail,
        public string $url,
        public string $customer,
        public ?Address $invoice = null,
        public ?array $items = null,
        public ?int $timeout = null,
    ) {}

    /**
     * @throws RandomException
     */
    public function toArray(): array
    {
        $data = [
            'salt' => bin2hex(random_bytes(16)),
            'merchant' => '',
            'orderRef' => $this->orderRef,
            'currency' => $this->currency->value,
            'total' => $this->total,
            'customerEmail' => $this->customerEmail,
            'customer' => $this->customer,
            'url' => $this->url,
            'timeout' => date('c', time() + ($this->timeout ?? config('simplepay.timeout', 600))),
            'sdkVersion' => 'SimplePay_PHP_SDK_2.1_Laravel',
        ];

        if ($this->invoice !== null) {
            $data['invoice'] = $this->invoice->toArray();
        }

        if ($this->items !== null) {
            $data['items'] = array_map(fn (Item $item) => $item->toArray(), $this->items);
        }

        return $data;
    }
}
