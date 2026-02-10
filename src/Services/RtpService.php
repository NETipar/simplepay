<?php

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
     * @throws RandomException|ConnectionException
     */
    public function query(Currency $currency, ?string $transactionId = null, ?string $orderRef = null): array
    {
        $resolved = $this->merchantResolver->resolve($currency);

        $payload = [
            'salt' => bin2hex(random_bytes(16)),
            'merchant' => $resolved['merchant'],
        ];

        if ($transactionId !== null) {
            $payload['transactionId'] = $transactionId;
        }

        if ($orderRef !== null) {
            $payload['orderRef'] = $orderRef;
        }

        return $this->client->post('/rtp/query', $payload, $resolved['secret_key']);
    }

    /**
     * @throws RandomException|ConnectionException
     */
    public function refund(Currency $currency, string $transactionId, float $amount): array
    {
        $resolved = $this->merchantResolver->resolve($currency);

        $payload = [
            'salt' => bin2hex(random_bytes(16)),
            'merchant' => $resolved['merchant'],
            'transactionId' => $transactionId,
            'amount' => $amount,
        ];

        return $this->client->post('/rtp/refund', $payload, $resolved['secret_key']);
    }

    /**
     * @throws RandomException|ConnectionException
     */
    public function reverse(Currency $currency, string $transactionId): array
    {
        $resolved = $this->merchantResolver->resolve($currency);

        $payload = [
            'salt' => bin2hex(random_bytes(16)),
            'merchant' => $resolved['merchant'],
            'transactionId' => $transactionId,
        ];

        return $this->client->post('/rtp/reverse', $payload, $resolved['secret_key']);
    }
}
