<?php

namespace Netipar\SimplePay\Services;

use Illuminate\Http\Client\ConnectionException;
use Netipar\SimplePay\Dto\ApplePayDoRequest;
use Netipar\SimplePay\Dto\ApplePayStartRequest;
use Netipar\SimplePay\Dto\BackResponse;
use Netipar\SimplePay\Dto\EamStartRequest;
use Netipar\SimplePay\Dto\FinishRequest;
use Netipar\SimplePay\Dto\FinishResponse;
use Netipar\SimplePay\Dto\IpnMessage;
use Netipar\SimplePay\Dto\PaymentRequest;
use Netipar\SimplePay\Dto\PaymentResponse;
use Netipar\SimplePay\Dto\QueryResponse;
use Netipar\SimplePay\Dto\RefundRequest;
use Netipar\SimplePay\Dto\RefundResponse;
use Netipar\SimplePay\Enums\Currency;
use Netipar\SimplePay\Support\Client;
use Netipar\SimplePay\Support\MerchantResolver;
use Netipar\SimplePay\Support\Signature;
use Random\RandomException;
use RuntimeException;

readonly class PaymentService
{
    public function __construct(
        private Client $client,
        private MerchantResolver $merchantResolver,
    ) {}

    /**
     * @throws RandomException
     * @throws ConnectionException
     */
    public function start(PaymentRequest $request): PaymentResponse
    {
        $merchant = $this->merchantResolver->getMerchant($request->currency);
        $secretKey = $this->merchantResolver->getSecretKey($request->currency);

        $payload = $request->toArray();
        $payload['merchant'] = $merchant;

        $response = $this->client->post('/v2/start', $payload, $secretKey);

        return PaymentResponse::fromArray($response);
    }

    /**
     * @throws RandomException
     * @throws ConnectionException
     */
    public function startEam(EamStartRequest $request): array
    {
        $merchant = $this->merchantResolver->getMerchant($request->currency);
        $secretKey = $this->merchantResolver->getSecretKey($request->currency);

        $payload = $request->toArray();
        $payload['merchant'] = $merchant;

        return $this->client->post('/v2/starteam', $payload, $secretKey);
    }

    /**
     * @throws ConnectionException
     */
    public function startApplePay(ApplePayStartRequest $request): array
    {
        $merchant = $this->merchantResolver->getMerchant($request->currency);
        $secretKey = $this->merchantResolver->getSecretKey($request->currency);

        $payload = $request->toArray();
        $payload['merchant'] = $merchant;

        return $this->client->post('/v2/startapplepay', $payload, $secretKey);
    }

    /**
     * @throws ConnectionException
     */
    public function doApplePay(ApplePayDoRequest $request): array
    {
        $merchant = $this->merchantResolver->getMerchant($request->currency);
        $secretKey = $this->merchantResolver->getSecretKey($request->currency);

        $payload = $request->toArray();
        $payload['merchant'] = $merchant;

        return $this->client->post('/v2/doapplepay', $payload, $secretKey);
    }

    public function handleBack(string $r, string $s): BackResponse
    {
        return new BackResponse($r, $s);
    }

    public function handleIpn(string $body, string $signature): IpnMessage
    {
        $data = json_decode($body, true);

        if (! is_array($data)) {
            throw new RuntimeException('Invalid JSON in IPN body');
        }

        $secretKey = match (true) {
            isset($data['currency']) => $this->merchantResolver->getSecretKey(Currency::from($data['currency'])),
            isset($data['merchant']) => $this->merchantResolver->getSecretKeyByMerchant($data['merchant']),
            default => throw new RuntimeException('IPN body must contain currency or merchant'),
        };

        if (! Signature::verify($secretKey, $body, $signature)) {
            throw new RuntimeException('Invalid IPN signature');
        }

        return IpnMessage::fromArray($data);
    }

    public function finish(FinishRequest $request): FinishResponse
    {
        $merchant = $this->merchantResolver->getMerchant($request->currency);
        $secretKey = $this->merchantResolver->getSecretKey($request->currency);

        $payload = $request->toArray();
        $payload['merchant'] = $merchant;

        $response = $this->client->post('/v2/finish', $payload, $secretKey);

        return FinishResponse::fromArray($response);
    }

    /**
     * @throws RandomException|ConnectionException
     */
    public function refund(RefundRequest $request): RefundResponse
    {
        $merchant = $this->merchantResolver->getMerchant($request->currency);
        $secretKey = $this->merchantResolver->getSecretKey($request->currency);

        $payload = $request->toArray();
        $payload['merchant'] = $merchant;

        $response = $this->client->post('/v2/refund', $payload, $secretKey);

        return RefundResponse::fromArray($response);
    }

    /**
     * @throws RandomException
     * @throws ConnectionException
     */
    public function query(Currency $currency, ?int $transactionId = null, ?string $orderRef = null): QueryResponse
    {
        $merchant = $this->merchantResolver->getMerchant($currency);
        $secretKey = $this->merchantResolver->getSecretKey($currency);

        $payload = [
            'salt' => bin2hex(random_bytes(16)),
            'merchant' => $merchant,
            'currency' => $currency->value,
            'sdkVersion' => 'SimplePay_PHP_SDK_2.1_Laravel',
        ];

        if ($transactionId !== null) {
            $payload['transactionIds'] = [$transactionId];
        }

        if ($orderRef !== null) {
            $payload['orderRefs'] = [$orderRef];
        }

        $response = $this->client->post('/v2/query', $payload, $secretKey);

        return QueryResponse::fromArray($response);
    }

    /**
     * @throws RandomException
     * @throws ConnectionException
     */
    public function cancel(Currency $currency, ?int $transactionId = null, ?string $orderRef = null): array
    {
        $merchant = $this->merchantResolver->getMerchant($currency);
        $secretKey = $this->merchantResolver->getSecretKey($currency);

        $payload = [
            'salt' => bin2hex(random_bytes(16)),
            'merchant' => $merchant,
            'currency' => $currency->value,
            'sdkVersion' => 'SimplePay_PHP_SDK_2.1_Laravel',
        ];

        if ($transactionId !== null) {
            $payload['transactionId'] = $transactionId;
        }

        if ($orderRef !== null) {
            $payload['orderRef'] = $orderRef;
        }

        return $this->client->post('/v2/transactionCancel', $payload, $secretKey);
    }
}
