<?php

use Illuminate\Support\Facades\Http;
use Netipar\SimplePay\Dto\CardStorageDoRequest;
use Netipar\SimplePay\Dto\Item;
use Netipar\SimplePay\Dto\RecurringRequest;
use Netipar\SimplePay\Enums\Currency;
use Netipar\SimplePay\Enums\TransactionType;
use Netipar\SimplePay\Services\CardStorageService;
use Netipar\SimplePay\Support\Client;
use Netipar\SimplePay\Support\MerchantResolver;

function createCardStorageService(): CardStorageService
{
    $merchantResolver = new MerchantResolver;

    return new CardStorageService(new Client, $merchantResolver);
}

it('sends a do request for oneclick payment', function () {
    Http::fake([
        'sandbox.simplepay.hu/payment/v2/do' => Http::response([
            'transactionId' => 123456,
            'total' => 1500,
            'merchant' => 'TEST_HUF_MERCHANT',
        ]),
    ]);

    $request = new CardStorageDoRequest(
        currency: Currency::HUF,
        transactionId: 123456,
        cardId: 'card-abc-123',
        orderRef: 'ORDER-001',
        total: 1500,
        customerEmail: 'test@example.com',
        url: 'https://example.com/back',
    );

    $response = createCardStorageService()->do($request);

    expect($response)->toHaveKey('transactionId', 123456);

    Http::assertSent(function ($httpRequest) {
        $body = json_decode($httpRequest->body(), true);

        return str_contains($httpRequest->url(), '/v2/do')
            && $body['merchant'] === 'TEST_HUF_MERCHANT'
            && $body['cardId'] === 'card-abc-123'
            && $body['orderRef'] === 'ORDER-001'
            && $body['total'] === 1500
            && $body['currency'] === 'HUF'
            && isset($body['salt']);
    });
});

it('sends a do request with items', function () {
    Http::fake([
        'sandbox.simplepay.hu/payment/v2/do' => Http::response([
            'transactionId' => 123456,
        ]),
    ]);

    $request = new CardStorageDoRequest(
        currency: Currency::HUF,
        transactionId: 123456,
        cardId: 'card-abc-123',
        orderRef: 'ORDER-002',
        total: 3000,
        customerEmail: 'test@example.com',
        url: 'https://example.com/back',
        items: [
            new Item(title: 'Product A', price: 1500, quantity: 2),
        ],
    );

    $response = createCardStorageService()->do($request);

    Http::assertSent(function ($httpRequest) {
        $body = json_decode($httpRequest->body(), true);

        return isset($body['items'])
            && $body['items'][0]['title'] === 'Product A';
    });
});

it('sends a dorecurring request', function () {
    Http::fake([
        'sandbox.simplepay.hu/payment/v2/dorecurring' => Http::response([
            'transactionId' => 789012,
            'total' => 5000,
        ]),
    ]);

    $request = new RecurringRequest(
        currency: Currency::HUF,
        transactionId: 789012,
        orderRef: 'ORDER-REC-001',
        total: 5000,
        customerEmail: 'test@example.com',
        url: 'https://example.com/back',
        type: TransactionType::Mit,
    );

    $response = createCardStorageService()->doRecurring($request);

    expect($response)->toHaveKey('transactionId', 789012);

    Http::assertSent(function ($httpRequest) {
        $body = json_decode($httpRequest->body(), true);

        return str_contains($httpRequest->url(), '/v2/dorecurring')
            && $body['merchant'] === 'TEST_HUF_MERCHANT'
            && $body['transactionId'] === 789012
            && $body['type'] === 'MIT'
            && isset($body['salt']);
    });
});

it('sends a cardquery request', function () {
    Http::fake([
        'sandbox.simplepay.hu/payment/v2/cardquery' => Http::response([
            'cardId' => 'card-abc-123',
            'status' => 'ACTIVE',
            'expiry' => '2025-12',
        ]),
    ]);

    $response = createCardStorageService()->cardQuery(Currency::HUF, 'card-abc-123');

    expect($response)
        ->toHaveKey('cardId', 'card-abc-123')
        ->toHaveKey('status', 'ACTIVE');

    Http::assertSent(function ($httpRequest) {
        $body = json_decode($httpRequest->body(), true);

        return str_contains($httpRequest->url(), '/v2/cardquery')
            && $body['merchant'] === 'TEST_HUF_MERCHANT'
            && $body['cardId'] === 'card-abc-123'
            && $body['history'] === false;
    });
});

it('sends a cardquery request with history', function () {
    Http::fake([
        'sandbox.simplepay.hu/payment/v2/cardquery' => Http::response([
            'cardId' => 'card-abc-123',
            'history' => [],
        ]),
    ]);

    createCardStorageService()->cardQuery(Currency::HUF, 'card-abc-123', history: true);

    Http::assertSent(function ($httpRequest) {
        $body = json_decode($httpRequest->body(), true);

        return $body['history'] === true;
    });
});

it('sends a cardcancel request', function () {
    Http::fake([
        'sandbox.simplepay.hu/payment/v2/cardcancel' => Http::response([
            'cardId' => 'card-abc-123',
            'status' => 'DISABLED',
        ]),
    ]);

    $response = createCardStorageService()->cardCancel(Currency::HUF, 'card-abc-123');

    expect($response)->toHaveKey('status', 'DISABLED');

    Http::assertSent(function ($httpRequest) {
        $body = json_decode($httpRequest->body(), true);

        return str_contains($httpRequest->url(), '/v2/cardcancel')
            && $body['merchant'] === 'TEST_HUF_MERCHANT'
            && $body['cardId'] === 'card-abc-123';
    });
});

it('sends a tokenquery request', function () {
    Http::fake([
        'sandbox.simplepay.hu/payment/v2/tokenquery' => Http::response([
            'token' => 'token-xyz-789',
            'status' => 'ACTIVE',
        ]),
    ]);

    $response = createCardStorageService()->tokenQuery(Currency::HUF, 'token-xyz-789');

    expect($response)
        ->toHaveKey('token', 'token-xyz-789')
        ->toHaveKey('status', 'ACTIVE');

    Http::assertSent(function ($httpRequest) {
        $body = json_decode($httpRequest->body(), true);

        return str_contains($httpRequest->url(), '/v2/tokenquery')
            && $body['merchant'] === 'TEST_HUF_MERCHANT'
            && $body['token'] === 'token-xyz-789';
    });
});

it('sends a tokencancel request', function () {
    Http::fake([
        'sandbox.simplepay.hu/payment/v2/tokencancel' => Http::response([
            'token' => 'token-xyz-789',
            'status' => 'DISABLED',
        ]),
    ]);

    $response = createCardStorageService()->tokenCancel(Currency::HUF, 'token-xyz-789');

    expect($response)->toHaveKey('status', 'DISABLED');

    Http::assertSent(function ($httpRequest) {
        $body = json_decode($httpRequest->body(), true);

        return str_contains($httpRequest->url(), '/v2/tokencancel')
            && $body['merchant'] === 'TEST_HUF_MERCHANT'
            && $body['token'] === 'token-xyz-789';
    });
});

it('uses the correct merchant for EUR currency', function () {
    Http::fake([
        'sandbox.simplepay.hu/payment/v2/cardquery' => Http::response([
            'cardId' => 'card-eur-123',
        ]),
    ]);

    createCardStorageService()->cardQuery(Currency::EUR, 'card-eur-123');

    Http::assertSent(function ($httpRequest) {
        $body = json_decode($httpRequest->body(), true);

        return $body['merchant'] === 'TEST_EUR_MERCHANT';
    });
});
