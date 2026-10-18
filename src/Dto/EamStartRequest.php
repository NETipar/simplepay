<?php

declare(strict_types=1);

namespace Netipar\SimplePay\Dto;

use Netipar\SimplePay\Enums\Currency;
use Random\RandomException;

readonly class EamStartRequest
{
    /**
     * @param  array<int, Item>|null  $items
     * @param  int  $eamType  What the generated deeplink is for: 1 QR, 2 deeplink, 3 NFC, 4 QR + deeplink, 5 QR + NFC, 6 deeplink + NFC, 7 all three.
     */
    public function __construct(
        public Currency $currency,
        public float $total,
        public string $orderRef,
        public string $customerEmail,
        public string $url,
        public ?array $items = null,
        public int $eamType = 1,
        public string $language = 'HU',
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
            'eamType' => $this->eamType,
            'timeout' => date('c', time() + ($this->timeout ?? (int) config('simplepay.timeout', 600))),
            'url' => $this->url,
            'items' => $this->items ? array_map(fn (Item $item) => $item->toArray(), $this->items) : null,
        ], fn (mixed $value): bool => $value !== null);
    }
}
