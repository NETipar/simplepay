<?php

declare(strict_types=1);

namespace Netipar\SimplePay\Dto;

use Netipar\SimplePay\Enums\Currency;
use Random\RandomException;

readonly class RtpStartRequest
{
    /**
     * The default payment deadline in seconds: 2 hours 5 minutes, as in the official SDK sample.
     */
    public const int DEFAULT_DEADLINE = 300 + 7200;

    /**
     * @param  array<int, Item>|null  $items
     * @param  RedirectUrls|null  $urls  Event-specific redirect URLs, including `pending`; SimplePay ignores $url when they are sent.
     * @param  int|null  $timeout  Seconds until the payment deadline when $deadline is not given (default: DEFAULT_DEADLINE).
     * @param  string|null  $deadline  Payment deadline (ISO 8601).
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
            'deadline' => $this->deadline ?? date('c', time() + ($this->timeout ?? self::DEFAULT_DEADLINE)),
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
