<?php

declare(strict_types=1);

namespace Netipar\SimplePay\Dto;

use Netipar\SimplePay\Enums\Currency;
use Random\RandomException;

readonly class RtpStartRequest
{
    /**
     * @param  array<int, Item>|null  $items
     * @param  RedirectUrls|null  $urls  Event-specific redirect URLs, including `pending`; SimplePay ignores $url when they are sent.
     * @param  string|null  $deadline  Payment deadline (ISO 8601); defaults to the same moment as the timeout.
     * @param  string|null  $additionalInfo  The announcement shown with the bank transfer.
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
        public ?RedirectUrls $urls = null,
        public string $language = 'HU',
        public ?string $deadline = null,
        public ?string $additionalInfo = null,
    ) {}

    /**
     * @return array<string, mixed>
     *
     * @throws RandomException
     */
    public function toArray(): array
    {
        $timeout = date('c', time() + ($this->timeout ?? (int) config('simplepay.timeout', 600)));

        $data = [
            'salt' => bin2hex(random_bytes(16)),
            'merchant' => '',
            'orderRef' => $this->orderRef,
            'currency' => $this->currency->value,
            'total' => $this->total,
            'customerEmail' => $this->customerEmail,
            'customer' => $this->customer,
            'url' => $this->url,
            'language' => $this->language,
            'timeout' => $timeout,
            'deadline' => $this->deadline ?? $timeout,
            'sdkVersion' => 'SimplePay_PHP_SDK_2.1_Laravel',
        ];

        if ($this->invoice !== null) {
            $data['invoice'] = $this->invoice->toArray();
        }

        if ($this->items !== null) {
            $data['items'] = array_map(fn (Item $item) => $item->toArray(), $this->items);
        }

        if ($this->additionalInfo !== null) {
            $data['additionalInfo'] = $this->additionalInfo;
        }

        if ($this->urls !== null) {
            $data['urls'] = $this->urls->toArray();
        }

        return $data;
    }
}
