<?php

namespace Netipar\SimplePay\Support;

class Signature
{
    public static function generate(string $merchantKey, string $data): string
    {
        return base64_encode(hash_hmac('sha384', $data, trim($merchantKey), true));
    }

    public static function verify(string $merchantKey, string $data, string $signature): bool
    {
        return hash_equals(self::generate($merchantKey, $data), $signature);
    }
}
