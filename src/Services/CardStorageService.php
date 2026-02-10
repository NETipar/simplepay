<?php

namespace Netipar\SimplePay\Services;

use Illuminate\Http\Client\ConnectionException;
use Netipar\SimplePay\Dto\CardStorageDoRequest;
use Netipar\SimplePay\Dto\RecurringRequest;
use Netipar\SimplePay\Enums\Currency;
use Netipar\SimplePay\Support\Client;
use Netipar\SimplePay\Support\MerchantResolver;
use Random\RandomException;

readonly class CardStorageService
{
    public function __construct(
        private Client $client,
        private MerchantResolver $merchantResolver,
    ) {}

    /**
     * @throws RandomException|ConnectionException
     */
    public function do(CardStorageDoRequest $request): array
    {
        $merchant = $this->merchantResolver->getMerchant($request->currency);
        $secretKey = $this->merchantResolver->getSecretKey($request->currency);

        $payload = $request->toArray();
        $payload['merchant'] = $merchant;

        return $this->client->post('/v2/do', $payload, $secretKey);
    }

    /**
     * @throws RandomException|ConnectionException
     */
    public function doRecurring(RecurringRequest $request): array
    {
        $merchant = $this->merchantResolver->getMerchant($request->currency);
        $secretKey = $this->merchantResolver->getSecretKey($request->currency);

        $payload = $request->toArray();
        $payload['merchant'] = $merchant;

        return $this->client->post('/v2/dorecurring', $payload, $secretKey);
    }

    /**
     * @throws RandomException|ConnectionException
     */
    public function cardQuery(Currency $currency, string $cardId, bool $history = false): array
    {
        $merchant = $this->merchantResolver->getMerchant($currency);
        $secretKey = $this->merchantResolver->getSecretKey($currency);

        $payload = [
            'salt' => bin2hex(random_bytes(16)),
            'merchant' => $merchant,
            'cardId' => $cardId,
            'history' => $history,
        ];

        return $this->client->post('/v2/cardquery', $payload, $secretKey);
    }

    /**
     * @throws RandomException|ConnectionException
     */
    public function cardCancel(Currency $currency, string $cardId): array
    {
        $merchant = $this->merchantResolver->getMerchant($currency);
        $secretKey = $this->merchantResolver->getSecretKey($currency);

        $payload = [
            'salt' => bin2hex(random_bytes(16)),
            'merchant' => $merchant,
            'cardId' => $cardId,
        ];

        return $this->client->post('/v2/cardcancel', $payload, $secretKey);
    }

    /**
     * @throws RandomException|ConnectionException
     */
    public function tokenQuery(Currency $currency, string $token): array
    {
        $merchant = $this->merchantResolver->getMerchant($currency);
        $secretKey = $this->merchantResolver->getSecretKey($currency);

        $payload = [
            'salt' => bin2hex(random_bytes(16)),
            'merchant' => $merchant,
            'token' => $token,
        ];

        return $this->client->post('/v2/tokenquery', $payload, $secretKey);
    }

    /**
     * @throws RandomException|ConnectionException
     */
    public function tokenCancel(Currency $currency, string $token): array
    {
        $merchant = $this->merchantResolver->getMerchant($currency);
        $secretKey = $this->merchantResolver->getSecretKey($currency);

        $payload = [
            'salt' => bin2hex(random_bytes(16)),
            'merchant' => $merchant,
            'token' => $token,
        ];

        return $this->client->post('/v2/tokencancel', $payload, $secretKey);
    }
}
