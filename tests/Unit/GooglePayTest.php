<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Netipar\SimplePay\Dto\Address;
use Netipar\SimplePay\Dto\GooglePayDoRequest;
use Netipar\SimplePay\Enums\Currency;
use Netipar\SimplePay\Exceptions\SimplePayApiException;
use Netipar\SimplePay\Services\PaymentService;

function googlePayRequest(Currency $currency = Currency::HUF): GooglePayDoRequest
{
    return new GooglePayDoRequest(
        currency: $currency,
        total: 1500.0,
        orderRef: 'ORD-GPAY-001',
        customerEmail: 'test@example.com',
        googlePayToken: base64_encode('{"signature":"test"}'),
        url: 'https://example.com/simplepay/back',
        threeDSReqAuthMethod: '02',
        invoice: new Address(
            name: 'Customer Kft.',
            country: 'hu',
            city: 'Budapest',
            zip: '1111',
            address: 'Main Street 1',
        ),
    );
}

it('sends the Google Pay token to the doGooglePay endpoint', function () {
    Http::fake([
        'sandbox.simplepay.hu/payment/v2/dogooglepay' => fakeSimplePayResponse([
            'transactionId' => 10312754,
            'orderRef' => 'ORD-GPAY-001',
            'currency' => 'HUF',
            'total' => 1500.0,
            'merchant' => 'TEST_HUF_MERCHANT',
        ], 'TEST_HUF_SECRET_KEY'),
    ]);

    $response = app(PaymentService::class)->doGooglePay(googlePayRequest());

    expect($response->transactionId)->toBe(10312754)
        ->and($response->orderRef)->toBe('ORD-GPAY-001')
        ->and($response->currency)->toBe(Currency::HUF)
        ->and($response->total)->toBe(1500.0)
        ->and($response->requiresChallenge())->toBeFalse()
        ->and($response->redirectUrl)->toBeNull();

    Http::assertSent(function ($request) {
        $body = json_decode($request->body(), true);

        return str_ends_with($request->url(), '/payment/v2/dogooglepay')
            && $body['merchant'] === 'TEST_HUF_MERCHANT'
            && $body['googlePayToken'] === base64_encode('{"signature":"test"}')
            && $body['currency'] === 'HUF'
            && (float) $body['total'] === 1500.0
            && $body['orderRef'] === 'ORD-GPAY-001'
            && $body['customerEmail'] === 'test@example.com'
            && $body['language'] === 'HU'
            && $body['type'] === 'CIT'
            && $body['threeDSReqAuthMethod'] === '02'
            && $body['url'] === 'https://example.com/simplepay/back'
            && $body['invoice']['city'] === 'Budapest'
            && $body['sdkVersion'] === 'SimplePay_PHP_SDK_2.1_Laravel'
            && isset($body['salt'], $body['timeout']);
    });
});

it('returns the 3DS challenge redirect instead of throwing', function () {
    Http::fake([
        'sandbox.simplepay.hu/payment/v2/dogooglepay' => fakeSimplePayResponse([
            'errorCodes' => [3004],
            'transactionId' => 10312754,
            'orderRef' => 'ORD-GPAY-001',
            'currency' => 'HUF',
            'total' => 1500.0,
            'redirectUrl' => 'https://sb-checkout.simplepay.hu/pay/pay/pspHU/challenge',
        ], 'TEST_HUF_SECRET_KEY'),
    ]);

    $response = app(PaymentService::class)->doGooglePay(googlePayRequest());

    expect($response->requiresChallenge())->toBeTrue()
        ->and($response->redirectUrl)->toBe('https://sb-checkout.simplepay.hu/pay/pay/pspHU/challenge')
        ->and($response->transactionId)->toBe(10312754);
});

it('throws when a 3004 response has no redirect url', function () {
    Http::fake([
        'sandbox.simplepay.hu/payment/v2/dogooglepay' => fakeSimplePayResponse([
            'errorCodes' => [3004],
            'transactionId' => 10312754,
            'orderRef' => 'ORD-GPAY-001',
        ], 'TEST_HUF_SECRET_KEY'),
    ]);

    app(PaymentService::class)->doGooglePay(googlePayRequest());
})->throws(SimplePayApiException::class);

it('throws when a 3004 response has no transaction id', function () {
    Http::fake([
        'sandbox.simplepay.hu/payment/v2/dogooglepay' => fakeSimplePayResponse([
            'errorCodes' => [3004],
            'redirectUrl' => 'https://sb-checkout.simplepay.hu/pay/pay/pspHU/challenge',
        ], 'TEST_HUF_SECRET_KEY'),
    ]);

    app(PaymentService::class)->doGooglePay(googlePayRequest());
})->throws(SimplePayApiException::class);

it('throws for other error codes', function () {
    Http::fake([
        'sandbox.simplepay.hu/payment/v2/dogooglepay' => fakeSimplePayResponse([
            'errorCodes' => [5321],
            'orderRef' => 'ORD-GPAY-001',
        ], 'TEST_HUF_SECRET_KEY'),
    ]);

    app(PaymentService::class)->doGooglePay(googlePayRequest());
})->throws(SimplePayApiException::class);

it('builds the Google Pay button config for the sandbox', function () {
    config()->set('simplepay.google_pay.merchant_id', 'BCR2DN4TEST');
    config()->set('simplepay.google_pay.merchant_name', 'Test Shop');

    expect(app(PaymentService::class)->googlePayConfig(Currency::EUR, 12.5))->toBe([
        'gpayMerchantId' => 'BCR2DN4TEST',
        'gpayMerchantName' => 'Test Shop',
        'gpayEnvironment' => 'TEST',
        'gateway' => 'simplepay',
        'gatewayMerchantId' => 'TEST_EUR_MERCHANT',
        'paymentType' => 'CARD',
        'total' => '12.5',
        'currency' => 'EUR',
    ]);
});

it('uses the production Google Pay environment outside the sandbox', function () {
    config()->set('simplepay.sandbox', false);

    $config = app(PaymentService::class)->googlePayConfig(Currency::HUF, 2500);

    expect($config['gpayEnvironment'])->toBe('PRODUCTION')
        ->and($config['gatewayMerchantId'])->toBe('TEST_HUF_MERCHANT')
        ->and($config['total'])->toBe('2500');
});
