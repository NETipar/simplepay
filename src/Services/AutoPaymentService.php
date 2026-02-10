<?php

namespace Netipar\SimplePay\Services;

use Illuminate\Http\Client\ConnectionException;
use Netipar\SimplePay\Dto\AutoPaymentRequest;
use Netipar\SimplePay\Support\Client;
use Netipar\SimplePay\Support\MerchantResolver;

readonly class AutoPaymentService
{
    public function __construct(
        private Client $client,
        private MerchantResolver $merchantResolver,
    ) {}

    /**
     * @throws ConnectionException
     */
    public function auto(AutoPaymentRequest $request): array
    {
        $merchant = $this->merchantResolver->getMerchant($request->currency);
        $secretKey = $this->merchantResolver->getSecretKey($request->currency);

        $payload = $request->toArray();
        $payload['merchant'] = $merchant;

        return $this->client->postSecurePay('/pay/pay/auto/pspHU', $payload, $secretKey);
    }
}
