<?php

declare(strict_types=1);

namespace Netipar\SimplePay\Dto;

use Netipar\SimplePay\Enums\Currency;
use Random\RandomException;

readonly class RtpDoRequest
{
    /**
     * The default payment deadline of the batch transactions, in seconds (30 days, as in the official SDK).
     */
    public const int DEFAULT_DEADLINE = 60 * 60 * 24 * 30;

    /**
     * @param  array<int, RtpPayment>  $payments
     * @param  string|null  $packageRef  Unique reference of the batch in your system; generated when missing.
     */
    public function __construct(
        public Currency $currency,
        public array $payments,
        public ?string $packageRef = null,
    ) {}

    /**
     * @return array<string, mixed>
     *
     * @throws RandomException
     */
    public function toArray(): array
    {
        return [
            'salt' => bin2hex(random_bytes(16)),
            'merchant' => '',
            'currency' => $this->currency->value,
            'packageRef' => $this->packageRef ?? 'pkg'.bin2hex(random_bytes(10)),
            'transactions' => array_map(
                fn (RtpPayment $payment): array => [...$payment->toArray(), 'currency' => $this->currency->value],
                $this->payments,
            ),
        ];
    }
}
