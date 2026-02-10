<?php

namespace Netipar\SimplePay\Dto;

use Netipar\SimplePay\Enums\BackEvent;
use Netipar\SimplePay\Support\MerchantResolver;
use Netipar\SimplePay\Support\Signature;
use RuntimeException;

readonly class BackResponse
{
    public BackEvent $event;

    public int $transactionId;

    public string $orderRef;

    public ?string $merchant;

    public ?float $total;

    public function __construct(string $r, string $s)
    {
        $decoded = base64_decode($r, true);

        if ($decoded === false) {
            throw new RuntimeException('Invalid base64 in back response r parameter');
        }

        $data = json_decode($decoded, true);

        if (! is_array($data)) {
            throw new RuntimeException('Invalid JSON in back response r parameter');
        }

        $this->event = BackEvent::from($data['e']);
        $this->transactionId = (int) $data['t'];
        $this->orderRef = $data['o'];
        $this->merchant = $data['m'] ?? null;
        $this->total = isset($data['total']) ? (float) $data['total'] : null;

        $merchantResolver = app(MerchantResolver::class);
        $secretKey = $this->resolveSecretKey($merchantResolver);

        if (! Signature::verify($secretKey, $decoded, $s)) {
            throw new RuntimeException('Invalid signature in back response');
        }
    }

    private function resolveSecretKey(MerchantResolver $merchantResolver): string
    {
        $merchants = config('simplepay.merchants');

        foreach ($merchants as $currencyKey => $config) {
            if (isset($config['merchant']) && $config['merchant'] === $this->merchant) {
                return $config['secret_key'];
            }
        }

        throw new RuntimeException("Cannot resolve secret key for merchant: {$this->merchant}");
    }
}
