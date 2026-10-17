<?php

declare(strict_types=1);

namespace Netipar\SimplePay\Dto;

/**
 * Event-specific redirect URLs (`urls`). When they are sent, SimplePay ignores the common `url`.
 * The `pending` URL is only used by Request to Pay transactions.
 */
readonly class RedirectUrls
{
    public function __construct(
        public string $success,
        public string $fail,
        public string $cancel,
        public string $timeout,
        public ?string $pending = null,
    ) {}

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        $urls = [
            'success' => $this->success,
            'fail' => $this->fail,
            'cancel' => $this->cancel,
            'timeout' => $this->timeout,
        ];

        if ($this->pending !== null) {
            $urls['pending'] = $this->pending;
        }

        return $urls;
    }
}
