<?php

namespace Netipar\SimplePay\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Netipar\SimplePay\Enums\Currency;
use Netipar\SimplePay\Support\MerchantResolver;
use Netipar\SimplePay\Support\Signature;
use Symfony\Component\HttpFoundation\Response;

class VerifySimplePaySignature
{
    public function __construct(
        private MerchantResolver $merchantResolver,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $signature = $request->header('Signature');

        if (! $signature) {
            abort(401, 'Missing SimplePay signature');
        }

        $body = $request->getContent();
        $data = json_decode($body, true);

        if (! is_array($data)) {
            abort(401, 'Invalid SimplePay request body');
        }

        $secretKey = $this->resolveSecretKey($data);

        if (! $secretKey) {
            abort(401, 'Cannot resolve SimplePay merchant for request');
        }

        if (! Signature::verify($secretKey, $body, $signature)) {
            abort(401, 'Invalid SimplePay signature');
        }

        return $next($request);
    }

    private function resolveSecretKey(array $data): ?string
    {
        if (isset($data['currency'])) {
            return $this->merchantResolver->getSecretKey(Currency::from($data['currency']));
        }

        if (isset($data['merchant'])) {
            return $this->merchantResolver->getSecretKeyByMerchant($data['merchant']);
        }

        return null;
    }
}
