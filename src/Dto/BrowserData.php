<?php

declare(strict_types=1);

namespace Netipar\SimplePay\Dto;

/**
 * The customer's browser data for the issuer's 3DS check, sent as the `browser` object.
 */
readonly class BrowserData
{
    /**
     * @param  string  $windowSize  Deprecated: not part of the SimplePay `browser` object, it is not sent.
     * @param  string|null  $challengePreference  Deprecated: not part of the SimplePay `browser` object, it is not sent.
     * @param  string|null  $ip  The customer's IP address, e.g. `$request->ip()`.
     */
    public function __construct(
        public string $accept,
        public string $agent,
        public string $language,
        public bool $javaEnabled,
        public int $colorDepth,
        public int $screenHeight,
        public int $screenWidth,
        public int $timeZone,
        public string $windowSize = '',
        public ?string $challengePreference = null,
        public ?string $ip = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'accept' => $this->accept,
            'agent' => $this->agent,
            'ip' => $this->ip,
            'java' => $this->javaEnabled,
            'lang' => $this->language,
            'color' => $this->colorDepth,
            'height' => $this->screenHeight,
            'width' => $this->screenWidth,
            'tz' => $this->timeZone,
        ], fn (mixed $value): bool => $value !== null);
    }
}
