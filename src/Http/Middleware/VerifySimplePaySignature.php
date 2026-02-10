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

        if (! is_array($data) || ! isset($data['currency'])) {
            abort(401, 'Invalid SimplePay request body');
        }

        $currency = Currency::from($data['currency']);
        $secretKey = $this->merchantResolver->getSecretKey($currency);

        if (! Signature::verify($secretKey, $body, $signature)) {
            abort(401, 'Invalid SimplePay signature');
        }

        return $next($request);
    }
}
