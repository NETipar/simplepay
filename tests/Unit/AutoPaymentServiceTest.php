<?php

use Illuminate\Support\Facades\Http;
use Netipar\SimplePay\Dto\Address;
use Netipar\SimplePay\Dto\AutoPaymentRequest;
use Netipar\SimplePay\Dto\BrowserData;
use Netipar\SimplePay\Dto\CardData;
use Netipar\SimplePay\Dto\Item;
use Netipar\SimplePay\Enums\Currency;
use Netipar\SimplePay\Enums\TransactionType;
use Netipar\SimplePay\Services\AutoPaymentService;
use Netipar\SimplePay\Support\Client;
use Netipar\SimplePay\Support\MerchantResolver;

function createAutoPaymentService(): AutoPaymentService
{
    $merchantResolver = new MerchantResolver;

    return new AutoPaymentService(new Client, $merchantResolver);
}

it('sends an auto payment request to securepay url', function () {
    Http::fake([
        'sandbox.simplepay.hu/pay/pay/auto/pspHU' => Http::response([
            'transactionId' => 555666,
            'total' => 2500,
            'merchant' => 'TEST_HUF_MERCHANT',
        ]),
    ]);

    $request = new AutoPaymentRequest(
        currency: Currency::HUF,
        total: 2500,
        orderRef: 'ORDER-AUTO-001',
        customerEmail: 'test@example.com',
        cardData: new CardData(
            cardNumber: '4111111111111111',
            expiry: '2512',
            cvc: '123',
        ),
        type: TransactionType::Cit,
    );

    $response = createAutoPaymentService()->auto($request);

    expect($response)->toHaveKey('transactionId', 555666);

    Http::assertSent(function ($httpRequest) {
        $body = json_decode($httpRequest->body(), true);

        return str_contains($httpRequest->url(), 'sandbox.simplepay.hu/pay/pay/auto/pspHU')
            && $body['merchant'] === 'TEST_HUF_MERCHANT'
            && $body['orderRef'] === 'ORDER-AUTO-001'
            && $body['total'] === 2500
            && $body['currency'] === 'HUF'
            && $body['type'] === 'CIT'
            && $body['cardData']['number'] === '4111111111111111'
            && $body['sdkVersion'] === 'netipar-simplepay:laravel'
            && isset($body['salt']);
    });
});

it('uses securepay base url not payment base url', function () {
    Http::fake([
        'sandbox.simplepay.hu/*' => Http::response(['transactionId' => 999]),
    ]);

    $request = new AutoPaymentRequest(
        currency: Currency::HUF,
        total: 1000,
        orderRef: 'ORDER-AUTO-002',
        customerEmail: 'test@example.com',
        cardData: new CardData(
            cardNumber: '4111111111111111',
            expiry: '2512',
            cvc: '123',
        ),
        type: TransactionType::Mit,
    );

    createAutoPaymentService()->auto($request);

    Http::assertSent(function ($httpRequest) {
        return str_contains($httpRequest->url(), 'sandbox.simplepay.hu/pay/')
            && ! str_contains($httpRequest->url(), '/payment/');
    });
});

it('includes browser data for 3ds when provided', function () {
    Http::fake([
        'sandbox.simplepay.hu/pay/pay/auto/pspHU' => Http::response([
            'transactionId' => 777888,
        ]),
    ]);

    $request = new AutoPaymentRequest(
        currency: Currency::HUF,
        total: 3000,
        orderRef: 'ORDER-3DS-001',
        customerEmail: 'test@example.com',
        cardData: new CardData(
            cardNumber: '4111111111111111',
            expiry: '2512',
            cvc: '123',
            cardHolder: 'Test User',
        ),
        type: TransactionType::Cit,
        browserData: new BrowserData(
            accept: 'text/html',
            agent: 'Mozilla/5.0',
            language: 'hu-HU',
            javaEnabled: false,
            colorDepth: 24,
            screenHeight: 1080,
            screenWidth: 1920,
            timeZone: -60,
            windowSize: '05',
        ),
    );

    $response = createAutoPaymentService()->auto($request);

    Http::assertSent(function ($httpRequest) {
        $body = json_decode($httpRequest->body(), true);

        return isset($body['browserData'])
            && $body['browserData']['accept'] === 'text/html'
            && $body['browserData']['agent'] === 'Mozilla/5.0'
            && $body['browserData']['language'] === 'hu-HU'
            && $body['browserData']['javaEnabled'] === false
            && $body['browserData']['colorDepth'] === 24
            && $body['browserData']['windowSize'] === '05'
            && $body['cardData']['holder'] === 'Test User';
    });
});

it('includes invoice and delivery addresses when provided', function () {
    Http::fake([
        'sandbox.simplepay.hu/pay/pay/auto/pspHU' => Http::response([
            'transactionId' => 999111,
        ]),
    ]);

    $request = new AutoPaymentRequest(
        currency: Currency::HUF,
        total: 5000,
        orderRef: 'ORDER-ADDR-001',
        customerEmail: 'test@example.com',
        cardData: new CardData(
            cardNumber: '4111111111111111',
            expiry: '2512',
            cvc: '123',
        ),
        type: TransactionType::Cit,
        customer: 'Test Customer',
        invoice: new Address(
            name: 'Invoice Name',
            country: 'HU',
            city: 'Budapest',
            zip: '1111',
            address: 'Test Street 1',
        ),
        delivery: new Address(
            name: 'Delivery Name',
            country: 'HU',
            city: 'Budapest',
            zip: '1111',
            address: 'Delivery Street 2',
        ),
        items: [
            new Item(title: 'Test Item', price: 2500, quantity: 2),
        ],
    );

    $response = createAutoPaymentService()->auto($request);

    Http::assertSent(function ($httpRequest) {
        $body = json_decode($httpRequest->body(), true);

        return $body['customer'] === 'Test Customer'
            && $body['invoice']['name'] === 'Invoice Name'
            && $body['delivery']['name'] === 'Delivery Name'
            && $body['items'][0]['title'] === 'Test Item';
    });
});

it('uses the correct merchant for EUR currency', function () {
    Http::fake([
        'sandbox.simplepay.hu/pay/pay/auto/pspHU' => Http::response([
            'transactionId' => 111222,
        ]),
    ]);

    $request = new AutoPaymentRequest(
        currency: Currency::EUR,
        total: 50,
        orderRef: 'ORDER-EUR-001',
        customerEmail: 'test@example.com',
        cardData: new CardData(
            cardNumber: '4111111111111111',
            expiry: '2512',
            cvc: '123',
        ),
        type: TransactionType::Cit,
    );

    createAutoPaymentService()->auto($request);

    Http::assertSent(function ($httpRequest) {
        $body = json_decode($httpRequest->body(), true);

        return $body['merchant'] === 'TEST_EUR_MERCHANT'
            && $body['currency'] === 'EUR';
    });
});
