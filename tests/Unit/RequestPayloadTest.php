<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Netipar\SimplePay\Dto\ApplePayDoRequest;
use Netipar\SimplePay\Dto\ApplePayStartRequest;
use Netipar\SimplePay\Dto\AutoPaymentRequest;
use Netipar\SimplePay\Dto\BrowserData;
use Netipar\SimplePay\Dto\CardData;
use Netipar\SimplePay\Dto\CardStorageDoRequest;
use Netipar\SimplePay\Dto\EamStartRequest;
use Netipar\SimplePay\Dto\RecurringRequest;
use Netipar\SimplePay\Dto\RtpDoRequest;
use Netipar\SimplePay\Dto\RtpPayment;
use Netipar\SimplePay\Dto\RtpStartRequest;
use Netipar\SimplePay\Enums\Currency;
use Netipar\SimplePay\Enums\TransactionType;
use Netipar\SimplePay\Services\AutoPaymentService;
use Netipar\SimplePay\Services\CardStorageService;
use Netipar\SimplePay\Services\PaymentService;
use Netipar\SimplePay\Services\RtpService;
use Psr\Log\LoggerInterface;

// The expected field names follow the official SimplePay PHP SDK samples
// (Payment 2.1.5, CardStorage 1.3, AutoPayment 2.4, RTP 0.9) and documentation.

beforeEach(function () {
    Http::fake(['*' => Http::response([
        'transactionId' => 123456,
        'orderRef' => 'ORD-1',
        'paymentUrl' => 'https://sandbox.simplepay.hu/pay/123456',
    ])]);
});

/**
 * @return list<array<string, mixed>>
 */
function sentBodies(): array
{
    return Http::recorded()
        ->map(fn (array $pair): array => json_decode($pair[0]->body(), true))
        ->values()
        ->all();
}

/**
 * @return array<string, mixed>
 */
function sentBody(): array
{
    $bodies = sentBodies();

    expect($bodies)->toHaveCount(1);

    return $bodies[0];
}

function browserData(): BrowserData
{
    return new BrowserData(
        accept: 'text/html',
        agent: 'Mozilla/5.0',
        language: 'hu-HU',
        javaEnabled: false,
        colorDepth: 24,
        screenHeight: 1080,
        screenWidth: 1920,
        timeZone: -120,
        windowSize: '05',
        ip: '203.0.113.10',
    );
}

/**
 * @return array<string, mixed>
 */
function officialBrowserFields(): array
{
    return [
        'accept' => 'text/html',
        'agent' => 'Mozilla/5.0',
        'ip' => '203.0.113.10',
        'java' => false,
        'lang' => 'hu-HU',
        'color' => 24,
        'height' => 1080,
        'width' => 1920,
        'tz' => -120,
    ];
}

it('sends the sdk version and the currency with card and token queries and cancels', function () {
    $service = app(CardStorageService::class);

    $service->cardQuery(Currency::HUF, 'card-1');
    $service->cardCancel(Currency::HUF, 'card-1');
    $service->tokenQuery(Currency::EUR, 'token-1');
    $service->tokenCancel(Currency::EUR, 'token-1');

    $bodies = sentBodies();

    expect(array_column($bodies, 'sdkVersion'))->toBe(array_fill(0, 4, 'SimplePay_PHP_SDK_2.1_Laravel'))
        ->and(array_column($bodies, 'currency'))->toBe(['HUF', 'HUF', 'EUR', 'EUR']);
});

it('sends a OneClick payment with the official do fields', function () {
    app(CardStorageService::class)->do(new CardStorageDoRequest(
        currency: Currency::HUF,
        transactionId: 123456,
        cardId: 'card-1',
        orderRef: 'ORD-1',
        total: 1500.0,
        customerEmail: 'test@example.com',
        url: 'https://example.com/back',
        cardSecret: 'secret',
        threeDSReqAuthMethod: '02',
        browser: browserData(),
    ));

    $body = sentBody();

    expect($body)->toMatchArray([
        'cardId' => 'card-1',
        'cardSecret' => 'secret',
        'type' => 'CIT',
        'methods' => ['CARD'],
        'threeDSReqAuthMethod' => '02',
        'url' => 'https://example.com/back',
        'sdkVersion' => 'SimplePay_PHP_SDK_2.1_Laravel',
    ])
        ->and($body['browser'])->toBe(officialBrowserFields())
        ->and($body)->not->toHaveKey('transactionId');
});

it('sends a recurring payment with the recurring token', function () {
    app(CardStorageService::class)->doRecurring(new RecurringRequest(
        currency: Currency::HUF,
        transactionId: 789012,
        orderRef: 'SUB-1',
        total: 9900.0,
        customerEmail: 'test@example.com',
        url: 'https://example.com/back',
        type: TransactionType::Mit,
        token: 'token-1',
        threeDSReqAuthMethod: '02',
        customer: 'Customer Name',
    ));

    $body = sentBody();

    expect($body)->toMatchArray([
        'token' => 'token-1',
        'type' => 'MIT',
        'methods' => ['CARD'],
        'threeDSReqAuthMethod' => '02',
        'customer' => 'Customer Name',
        'sdkVersion' => 'SimplePay_PHP_SDK_2.1_Laravel',
    ])
        ->and($body)->not->toHaveKey('transactionId');
});

it('refuses a recurring payment without a recurring token', function () {
    (new RecurringRequest(
        currency: Currency::HUF,
        transactionId: 789012,
        orderRef: 'SUB-1',
        total: 9900.0,
        customerEmail: 'test@example.com',
        url: 'https://example.com/back',
        type: TransactionType::Mit,
    ))->toArray();
})->throws(InvalidArgumentException::class);

it('sends an auto payment with the official browser data and 3DS fields', function () {
    app(AutoPaymentService::class)->auto(new AutoPaymentRequest(
        currency: Currency::HUF,
        total: 100.0,
        orderRef: 'AUTO-1',
        customerEmail: 'test@example.com',
        cardData: new CardData(cardNumber: '4908366099900425', expiry: '0130', cvc: '123', cardHolder: 'Test Holder'),
        type: TransactionType::Cit,
        browserData: browserData(),
        url: 'https://example.com/back',
        threeDSReqAuthMethod: '02',
        language: 'HU',
    ));

    $body = sentBody();

    expect($body['browser'])->toBe(officialBrowserFields())
        ->and($body)->toMatchArray([
            'url' => 'https://example.com/back',
            'threeDSReqAuthMethod' => '02',
            'language' => 'HU',
            'sdkVersion' => 'SimplePay_PHP_SDK_2.1_Laravel',
        ])
        ->and($body)->not->toHaveKey('browserData');
});

it('sends a Request to Pay start with language, deadline and announcement', function () {
    app(RtpService::class)->start(new RtpStartRequest(
        currency: Currency::HUF,
        total: 5000.0,
        orderRef: 'RTP-1',
        customerEmail: 'test@example.com',
        url: 'https://example.com/back',
        customer: 'Customer Name',
        language: 'EN',
        deadline: '2026-11-01T12:00:00+01:00',
        additionalInfo: 'Invoice 42',
    ));

    expect(sentBody())->toMatchArray([
        'language' => 'EN',
        'deadline' => '2026-11-01T12:00:00+01:00',
        'additionalInfo' => 'Invoice 42',
    ]);
});

it('defaults the language and deadline of a Request to Pay start', function () {
    app(RtpService::class)->start(new RtpStartRequest(
        currency: Currency::HUF,
        total: 5000.0,
        orderRef: 'RTP-1',
        customerEmail: 'test@example.com',
        url: 'https://example.com/back',
        customer: 'Customer Name',
    ));

    $body = sentBody();

    expect($body['language'])->toBe('HU')
        ->and($body['deadline'])->toBe($body['timeout']);
});

it('sends a Request to Pay batch as a package of transactions', function () {
    app(RtpService::class)->do(new RtpDoRequest(
        currency: Currency::HUF,
        payments: [
            new RtpPayment(
                orderRef: 'RTP-1',
                total: 1000.0,
                customerEmail: 'a@example.com',
                customer: 'Customer A',
                deadline: '2026-11-30T12:00:00+01:00',
                additionalInfo: 'Membership fee',
                customerBankAccount: '002000041111111111111111',
            ),
        ],
        packageRef: 'PKG-1',
    ));

    $body = sentBody();

    expect($body)->toHaveKey('packageRef', 'PKG-1')
        ->not->toHaveKey('payments')
        ->and($body['transactions'][0])->toMatchArray([
            'orderRef' => 'RTP-1',
            'total' => 1000.0,
            'currency' => 'HUF',
            'customerEmail' => 'a@example.com',
            'customer' => 'Customer A',
            'deadline' => '2026-11-30T12:00:00+01:00',
            'additionalInfo' => 'Membership fee',
            'customerBankAccount' => '002000041111111111111111',
            'language' => 'HU',
        ]);
});

it('generates the package reference and the deadlines of a Request to Pay batch when missing', function () {
    app(RtpService::class)->do(new RtpDoRequest(
        currency: Currency::HUF,
        payments: [new RtpPayment(orderRef: 'RTP-1', total: 1000.0, customerEmail: 'a@example.com', customer: 'Customer A')],
    ));

    $body = sentBody();

    expect($body['packageRef'])->toStartWith('pkg')
        ->and(strtotime($body['transactions'][0]['deadline']))->toBeGreaterThan(time() + 29 * 86400);
});

it('queries Request to Pay transactions with id lists and without currency', function () {
    $service = app(RtpService::class);

    $service->query(Currency::HUF, transactionId: '168867693');
    $service->query(Currency::HUF, orderRef: 'RTP-1');
    $service->query(Currency::HUF, packageRef: 'PKG-1');

    [$byId, $byOrderRef, $byPackage] = sentBodies();

    expect($byId['transactionIds'])->toBe(['168867693'])
        ->and($byId)->not->toHaveKey('transactionId')
        ->and($byId)->not->toHaveKey('currency')
        ->and($byOrderRef['orderRefs'])->toBe(['RTP-1'])
        ->and($byPackage['packageRef'])->toBe('PKG-1')
        ->and($byPackage['sdkVersion'])->toBe('SimplePay_PHP_SDK_2.1_Laravel');
});

it('refunds a Request to Pay with the official refund fields', function () {
    app(RtpService::class)->refund(Currency::HUF, '102264132', 15.0, originalTotal: 25.0);

    expect(sentBody())->toMatchArray([
        'transactionId' => '102264132',
        'refundTotal' => 15.0,
        'originalTotal' => 25.0,
        'currency' => 'HUF',
    ])->not->toHaveKey('amount');
});

it('reverses a Request to Pay with the currency', function () {
    app(RtpService::class)->reverse(Currency::EUR, '102264204');

    expect(sentBody())->toMatchArray(['transactionId' => '102264204', 'currency' => 'EUR']);
});

it('starts an Apple Pay session with the registered domain', function () {
    app(PaymentService::class)->startApplePay(new ApplePayStartRequest(
        currency: Currency::HUF,
        total: 10.0,
        orderRef: 'AP-1',
        customerEmail: 'test@example.com',
        url: 'https://example.com/back',
        domain: 'shop.example.com',
        language: 'EN',
    ));

    expect(sentBody())->toMatchArray([
        'domain' => 'shop.example.com',
        'language' => 'EN',
        'sdkVersion' => 'SimplePay_PHP_SDK_2.1_Laravel',
    ])->toHaveKey('timeout');
});

it('uses the host of the app url as the default Apple Pay domain', function () {
    config()->set('app.url', 'https://checkout.example.com/shop');

    app(PaymentService::class)->startApplePay(new ApplePayStartRequest(
        currency: Currency::HUF,
        total: 10.0,
        orderRef: 'AP-1',
        customerEmail: 'test@example.com',
        url: 'https://example.com/back',
    ));

    expect(sentBody())->toMatchArray(['domain' => 'checkout.example.com', 'language' => 'HU']);
});

it('completes an Apple Pay payment with the applePayToken field', function () {
    app(PaymentService::class)->doApplePay(new ApplePayDoRequest(
        currency: Currency::HUF,
        transactionId: 581841992,
        orderRef: 'AP-1',
        token: 'apple-pay-token',
    ));

    expect(sentBody())->toMatchArray([
        'transactionId' => 581841992,
        'applePayToken' => 'apple-pay-token',
        'sdkVersion' => 'SimplePay_PHP_SDK_2.1_Laravel',
    ])->not->toHaveKey('token');
});

it('starts a qvik payment with the eam type, language and timeout', function () {
    app(PaymentService::class)->startEam(new EamStartRequest(
        currency: Currency::HUF,
        total: 3000.0,
        orderRef: 'EAM-1',
        customerEmail: 'test@example.com',
        url: 'https://example.com/back',
        eamType: 4,
        language: 'EN',
    ));

    expect(sentBody())->toMatchArray([
        'eamType' => 4,
        'language' => 'EN',
        'sdkVersion' => 'SimplePay_PHP_SDK_2.1_Laravel',
    ])->toHaveKey('timeout');
});

it('masks payment credentials in the request log', function () {
    config()->set('simplepay.log_channel', 'simplepay');

    $channel = Mockery::spy(LoggerInterface::class);
    Log::shouldReceive('channel')->with('simplepay')->andReturn($channel);

    app(AutoPaymentService::class)->auto(new AutoPaymentRequest(
        currency: Currency::HUF,
        total: 100.0,
        orderRef: 'AUTO-1',
        customerEmail: 'test@example.com',
        cardData: new CardData(cardNumber: '4908366099900425', expiry: '0130', cvc: '123', cardHolder: 'Test Holder'),
        type: TransactionType::Cit,
    ));

    $channel->shouldHaveReceived('debug')->withArgs(function (string $message, array $context): bool {
        if ($message !== 'SimplePay securepay request') {
            return false;
        }

        expect($context['data']['cardData'])->toBe([
            'number' => '[masked]',
            'expiry' => '[masked]',
            'cvc' => '[masked]',
            'holder' => 'Test Holder',
        ]);

        return true;
    })->once();

    expect(sentBody()['cardData']['number'])->toBe('4908366099900425');
});
