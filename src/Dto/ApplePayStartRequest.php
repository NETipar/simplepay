<?php

declare(strict_types=1);

namespace Netipar\SimplePay\Dto;

use Netipar\SimplePay\Enums\Currency;
use Random\RandomException;

readonly class ApplePayStartRequest
{
    /**
     * @param  string|null  $domain  The FQDN registered for Apple Pay at SimplePay; defaults to the host of `app.url`.
     */
    public function __construct(
        public Currency $currency,
        public float $total,
        public string $orderRef,
        public string $customerEmail,
        public string $url,
        public ?string $domain = null,
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
            'domain' => $this->domain ?? parse_url((string) config('app.url'), PHP_URL_HOST),
            'timeout' => date('c', time() + ($this->timeout ?? (int) config('simplepay.timeout', 600))),
            'url' => $this->url,
        ], fn (mixed $value): bool => $value !== null && $value !== false);
    }
}
