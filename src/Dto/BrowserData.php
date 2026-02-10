<?php

namespace Netipar\SimplePay\Dto;

readonly class BrowserData
{
    public function __construct(
        public string $accept,
        public string $agent,
        public string $language,
        public bool $javaEnabled,
        public int $colorDepth,
        public int $screenHeight,
        public int $screenWidth,
        public int $timeZone,
        public string $windowSize,
        public ?string $challengePreference = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'accept' => $this->accept,
            'agent' => $this->agent,
            'language' => $this->language,
            'javaEnabled' => $this->javaEnabled,
            'colorDepth' => $this->colorDepth,
            'screenHeight' => $this->screenHeight,
            'screenWidth' => $this->screenWidth,
            'timeZone' => $this->timeZone,
            'windowSize' => $this->windowSize,
            'challengePreference' => $this->challengePreference,
        ], fn ($value) => $value !== null);
    }
}
