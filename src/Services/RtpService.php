<?php

declare(strict_types=1);

namespace Netipar\SimplePay\Services;

use Illuminate\Http\Client\ConnectionException;
use Netipar\SimplePay\Dto\PaymentResponse;
use Netipar\SimplePay\Dto\RtpDoRequest;
use Netipar\SimplePay\Dto\RtpStartRequest;
use Netipar\SimplePay\Enums\Currency;
use Netipar\SimplePay\Support\Client;
use Netipar\SimplePay\Support\MerchantResolver;
use Random\RandomException;

readonly class RtpService
{
    public function __construct(
        private Client $client,
        private MerchantResolver $merchantResolver,
    ) {}

    /**
     * @throws RandomException
     * @throws ConnectionException
     */
    public function start(RtpStartRequest $request): PaymentResponse
    {
        $resolved = $this->merchantResolver->resolve($request->currency);
        $payload = $request->toArray();
        $payload['merchant'] = $resolved['merchant'];

        $response = $this->client->post('/rtp/start', $payload, $resolved['secret_key']);

        return PaymentResponse::fromArray($response);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws RandomException
     * @throws ConnectionException
     */
    public function do(RtpDoRequest $request): array
    {
        $resolved = $this->merchantResolver->resolve($request->currency);
        $payload = $request->toArray();
        $payload['merchant'] = $resolved['merchant'];

        return $this->client->post('/rtp/do', $payload, $resolved['secret_key']);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws RandomException|ConnectionException
     */
    public function query(Currency $currency, ?string $transactionId = null, ?string $orderRef = null, ?string $packageRef = null): array
    {
        $resolved = $this->merchantResolver->resolve($currency);

        $payload = [
            'salt' => bin2hex(random_bytes(16)),
            'merchant' => $resolved['merchant'],
        ];

        if ($transactionId !== null) {
            $payload['transactionIds'] = [$transactionId];
        }

        if ($orderRef !== null) {
            $payload['orderRefs'] = [$orderRef];
        }

        if ($packageRef !== null) {
            $payload['packageRef'] = $packageRef;
        }

        return $this->client->post('/rtp/query', $payload, $resolved['secret_key']);
    }

    /**
     * @param  float  $amount  The amount to refund (`refundTotal`).
     * @param  float|null  $originalTotal  The original amount of the transaction, as the official SDK sends it.
     * @return array<string, mixed>
     *
     * @throws RandomException|ConnectionException
     */
    public function refund(Currency $currency, string $transactionId, float $amount, ?float $originalTotal = null): array
    {
        $resolved = $this->merchantResolver->resolve($currency);

        $payload = [
            'salt' => bin2hex(random_bytes(16)),
            'merchant' => $resolved['merchant'],
            'currency' => $currency->value,
            'transactionId' => $transactionId,
            'refundTotal' => $amount,
        ];

        if ($originalTotal !== null) {
            $payload['originalTotal'] = $originalTotal;
        }

        return $this->client->post('/rtp/refund', $payload, $resolved['secret_key']);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws RandomException|ConnectionException
     */
    public function reverse(Currency $currency, string $transactionId): array
    {
        $resolved = $this->merchantResolver->resolve($currency);

        $payload = [
            'salt' => bin2hex(random_bytes(16)),
            'merchant' => $resolved['merchant'],
            'currency' => $currency->value,
            'transactionId' => $transactionId,
        ];

        return $this->client->post('/rtp/reverse', $payload, $resolved['secret_key']);
    }
}
