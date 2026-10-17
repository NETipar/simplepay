<?php

declare(strict_types=1);

namespace Netipar\SimplePay\Support;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Netipar\SimplePay\Exceptions\SimplePayApiException;
use RuntimeException;

class Client
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     *
     * @throws ConnectionException
     * @throws SimplePayApiException
     */
    public function post(string $endpoint, array $data, string $merchantKey): array
    {
        $url = $this->buildUrl($endpoint);
        $json = json_encode($data, JSON_THROW_ON_ERROR);
        $signature = Signature::generate($merchantKey, $json);

        $this->log('SimplePay request', ['url' => $url, 'data' => $data]);

        $response = Http::withHeaders([
            'Signature' => $signature,
        ])->withBody($json)->post($url);

        $this->validateResponse($response, $merchantKey);

        $responseData = $response->json();

        $this->log('SimplePay response', ['url' => $url, 'data' => $responseData]);

        return $responseData;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     *
     * @throws ConnectionException
     * @throws SimplePayApiException
     */
    public function postSecurePay(string $endpoint, array $data, string $merchantKey): array
    {
        $url = $this->buildSecurePayUrl($endpoint);
        $json = json_encode($data, JSON_THROW_ON_ERROR);
        $signature = Signature::generate($merchantKey, $json);

        $this->log('SimplePay securepay request', ['url' => $url, 'data' => $data]);

        $response = Http::withHeaders([
            'Signature' => $signature,
        ])->withBody($json)->post($url);

        $this->validateResponse($response, $merchantKey);

        $responseData = $response->json();

        $this->log('SimplePay securepay response', ['url' => $url, 'data' => $responseData]);

        return $responseData;
    }

    public function isSandbox(): bool
    {
        return (bool) config('simplepay.sandbox');
    }

    private function buildUrl(string $endpoint): string
    {
        $base = $this->isSandbox() ? config('simplepay.api.sandbox') : config('simplepay.api.live');

        return "{$base}{$endpoint}";
    }

    private function buildSecurePayUrl(string $endpoint): string
    {
        $base = $this->isSandbox() ? config('simplepay.api.sandbox_securepay') : config('simplepay.api.live_securepay');

        return "{$base}{$endpoint}";
    }

    private function validateResponse(Response $response, string $merchantKey): void
    {
        if (! $response->successful()) {
            throw new RuntimeException("SimplePay API error: HTTP {$response->status()} - {$response->body()}");
        }

        $responseSignature = $response->header('Signature');

        if ($responseSignature && ! Signature::verify($merchantKey, $response->body(), $responseSignature)) {
            throw new RuntimeException('SimplePay response signature verification failed');
        }

        $data = $response->json();

        if (is_array($data) && ! empty($data['errorCodes'])) {
            throw new SimplePayApiException($data['errorCodes'], $data);
        }
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function log(string $message, array $context = []): void
    {
        $channel = config('simplepay.log_channel');

        if (! $channel) {
            return;
        }

        Log::channel($channel)->debug($message, $context);
    }
}
