<?php

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Support\Facades\Http;
use Netipar\SimplePay\Dto\FinishRequest;
use Netipar\SimplePay\Dto\PaymentRequest;
use Netipar\SimplePay\Dto\RefundRequest;
use Netipar\SimplePay\Enums\Currency;
use Netipar\SimplePay\Enums\PaymentMethod;
use Netipar\SimplePay\Exceptions\SimplePayApiException;
use Netipar\SimplePay\Services\PaymentService;
use Netipar\SimplePay\Support\Signature;

function fakeSimplePayResponse(array $data, string $secretKey): PromiseInterface
{
    $json = json_encode($data);
    $signature = Signature::generate($secretKey, $json);

    return Http::response($data, 200, [
        'Signature' => $signature,
    ]);
}

it('sends a start request and returns PaymentResponse', function () {
    $secretKey = 'TEST_HUF_SECRET_KEY';

    Http::fake([
        'sandbox.simplepay.hu/payment/v2/start' => fakeSimplePayResponse([
            'transactionId' => 12345,
            'paymentUrl' => 'https://sandbox.simplepay.hu/pay/12345',
            'timeout' => '2026-02-10T12:00:00+01:00',
        ], $secretKey),
    ]);

    $service = app(PaymentService::class);

    $request = new PaymentRequest(
        currency: Currency::HUF,
        total: 1500.0,
        orderRef: 'ORD-001',
        customerEmail: 'test@example.com',
        language: 'HU',
        url: 'https://example.com/payment/back',
        methods: [PaymentMethod::CARD],
    );

    $response = $service->start($request);

    expect($response->transactionId)->toBe(12345);
    expect($response->paymentUrl)->toBe('https://sandbox.simplepay.hu/pay/12345');

    Http::assertSent(function ($request) {
        $body = json_decode($request->body(), true);

        return str_contains($request->url(), '/v2/start')
            && $body['merchant'] === 'TEST_HUF_MERCHANT'
            && $body['currency'] === 'HUF'
            && $body['orderRef'] === 'ORD-001'
            && (float) $body['total'] === 1500.0
            && $body['customerEmail'] === 'test@example.com'
            && $body['sdkVersion'] === 'SimplePay_PHP_SDK_2.1_Laravel';
    });
});

it('handles back response', function () {
    $service = app(PaymentService::class);

    $data = json_encode([
        'e' => 'SUCCESS',
        't' => 12345,
        'o' => 'ORD-001',
        'm' => 'TEST_HUF_MERCHANT',
    ]);

    $r = base64_encode($data);
    $s = Signature::generate('TEST_HUF_SECRET_KEY', $data);

    $backResponse = $service->handleBack($r, $s);

    expect($backResponse->event->value)->toBe('SUCCESS');
    expect($backResponse->transactionId)->toBe(12345);
    expect($backResponse->orderRef)->toBe('ORD-001');
});

it('handles IPN message', function () {
    $service = app(PaymentService::class);

    $body = json_encode([
        'orderRef' => 'ORD-001',
        'transactionId' => 12345,
        'status' => 'FINISHED',
        'currency' => 'HUF',
        'total' => 1500,
        'finishDate' => '2026-02-10T12:00:00+01:00',
        'merchant' => 'TEST_HUF_MERCHANT',
    ]);

    $signature = Signature::generate('TEST_HUF_SECRET_KEY', $body);

    $ipn = $service->handleIpn($body, $signature);

    expect($ipn->orderRef)->toBe('ORD-001');
    expect($ipn->transactionId)->toBe(12345);
    expect($ipn->status->value)->toBe('FINISHED');
    expect($ipn->currency)->toBe(Currency::HUF);
    expect($ipn->total)->toBe(1500.0);
});

it('rejects IPN with invalid signature', function () {
    $service = app(PaymentService::class);

    $body = json_encode([
        'orderRef' => 'ORD-001',
        'transactionId' => 12345,
        'status' => 'FINISHED',
        'currency' => 'HUF',
        'total' => 1500,
    ]);

    $service->handleIpn($body, 'invalid-signature');
})->throws(RuntimeException::class, 'Invalid IPN signature');

it('sends a finish request', function () {
    $secretKey = 'TEST_HUF_SECRET_KEY';

    Http::fake([
        'sandbox.simplepay.hu/payment/v2/finish' => fakeSimplePayResponse([
            'transactionId' => 12345,
            'approveTotal' => 1500.0,
        ], $secretKey),
    ]);

    $service = app(PaymentService::class);

    $request = new FinishRequest(
        currency: Currency::HUF,
        transactionId: 12345,
        orderRef: 'ORD-001',
        amount: 1500.0,
        originalTotal: 1500.0,
    );

    $response = $service->finish($request);

    expect($response->transactionId)->toBe(12345);
    expect($response->total)->toBe(1500.0);

    Http::assertSent(function ($request) {
        $body = json_decode($request->body(), true);

        return str_contains($request->url(), '/v2/finish')
            && $body['merchant'] === 'TEST_HUF_MERCHANT'
            && $body['transactionId'] === 12345;
    });
});

it('sends a refund request', function () {
    $secretKey = 'TEST_HUF_SECRET_KEY';

    Http::fake([
        'sandbox.simplepay.hu/payment/v2/refund' => fakeSimplePayResponse([
            'transactionId' => 12345,
            'remainingTotal' => 500.0,
        ], $secretKey),
    ]);

    $service = app(PaymentService::class);

    $request = new RefundRequest(
        currency: Currency::HUF,
        transactionId: 12345,
        orderRef: 'ORD-001',
        amount: 1000.0,
    );

    $response = $service->refund($request);

    expect($response->transactionId)->toBe(12345);
    expect($response->remainingTotal)->toBe(500.0);

    Http::assertSent(function ($request) {
        $body = json_decode($request->body(), true);

        return str_contains($request->url(), '/v2/refund')
            && (float) $body['refundTotal'] === 1000.0;
    });
});

it('sends a query request by transaction id', function () {
    $secretKey = 'TEST_HUF_SECRET_KEY';

    Http::fake([
        'sandbox.simplepay.hu/payment/v2/query' => fakeSimplePayResponse([
            'merchant' => 'TEST_HUF_MERCHANT',
            'transactions' => [
                [
                    'transactionId' => 12345,
                    'orderRef' => 'ORD-001',
                    'status' => 'FINISHED',
                    'total' => 1500.0,
                    'currency' => 'HUF',
                    'merchant' => 'TEST_HUF_MERCHANT',
                    'remainingTotal' => 0,
                    'paymentDate' => '2026-02-10T12:00:00+01:00',
                    'finishDate' => '2026-02-10T12:01:00+01:00',
                    'method' => 'CARD',
                ],
            ],
            'totalCount' => 1,
        ], $secretKey),
    ]);

    $service = app(PaymentService::class);

    $response = $service->query(Currency::HUF, transactionId: 12345);

    expect($response->transactionId)->toBe(12345);
    expect($response->status->value)->toBe('FINISHED');
    expect($response->method)->toBe('CARD');

    Http::assertSent(function ($request) {
        $body = json_decode($request->body(), true);

        return str_contains($request->url(), '/v2/query')
            && $body['transactionIds'] === [12345]
            && $body['merchant'] === 'TEST_HUF_MERCHANT';
    });
});

it('sends a cancel request', function () {
    $secretKey = 'TEST_HUF_SECRET_KEY';

    Http::fake([
        'sandbox.simplepay.hu/payment/v2/transactionCancel' => fakeSimplePayResponse([
            'transactionId' => 12345,
            'status' => 'CANCELLED',
        ], $secretKey),
    ]);

    $service = app(PaymentService::class);

    $response = $service->cancel(Currency::HUF, transactionId: 12345);

    expect($response['transactionId'])->toBe(12345);

    Http::assertSent(function ($request) {
        return str_contains($request->url(), '/v2/transactionCancel');
    });
});

it('builds PaymentRequest payload with optional fields', function () {
    $request = new PaymentRequest(
        currency: Currency::HUF,
        total: 2500.0,
        orderRef: 'ORD-002',
        customerEmail: 'test@example.com',
        language: 'HU',
        url: 'https://example.com/back',
        methods: [PaymentMethod::CARD, PaymentMethod::WIRE],
        twoStep: true,
        shippingPrice: 500.0,
        discount: 100.0,
        customer: 'CUST-001',
    );

    $data = $request->toArray();

    expect($data['twoStep'])->toBeTrue();
    expect($data['shippingPrice'])->toBe(500.0);
    expect($data['discount'])->toBe(100.0);
    expect($data['customer'])->toBe('CUST-001');
    expect($data['methods'])->toBe(['CARD', 'WIRE']);
    expect($data['total'])->toBe(2500.0);
    expect($data)->toHaveKey('salt');
    expect($data)->toHaveKey('timeout');
});

it('builds PaymentRequest payload without optional fields', function () {
    $request = new PaymentRequest(
        currency: Currency::EUR,
        total: 100.0,
        orderRef: 'ORD-003',
        customerEmail: 'test@example.com',
        language: 'EN',
        url: 'https://example.com/back',
    );

    $data = $request->toArray();

    expect($data)->not->toHaveKey('twoStep');
    expect($data)->not->toHaveKey('shippingPrice');
    expect($data)->not->toHaveKey('discount');
    expect($data)->not->toHaveKey('invoice');
    expect($data)->not->toHaveKey('delivery');
    expect($data)->not->toHaveKey('items');
});

it('throws SimplePayApiException when API returns errorCodes', function () {
    $secretKey = 'TEST_HUF_SECRET_KEY';

    Http::fake([
        'sandbox.simplepay.hu/payment/v2/start' => fakeSimplePayResponse([
            'errorCodes' => [5083],
            'orderRef' => 'ORD-ERR',
            'merchant' => 'TEST_HUF_MERCHANT',
        ], $secretKey),
    ]);

    $service = app(PaymentService::class);

    $request = new PaymentRequest(
        currency: Currency::HUF,
        total: 1500.0,
        orderRef: 'ORD-ERR',
        customerEmail: 'test@example.com',
        language: 'HU',
        url: 'https://example.com/back',
        methods: [PaymentMethod::CARD],
    );

    $service->start($request);
})->throws(SimplePayApiException::class, '[5083] Token times szükséges');

it('provides error codes and descriptions on SimplePayApiException', function () {
    $exception = new SimplePayApiException([5083, 5044]);

    expect($exception->getErrorCodes())->toBe([5083, 5044]);
    expect($exception->hasErrorCode(5083))->toBeTrue();
    expect($exception->hasErrorCode(9999))->toBeFalse();
    expect($exception->getMessage())->toBe('[5083] Token times szükséges; [5044] Recurring nincs engedélyezve');
    expect($exception->getResolvedCodes())->toHaveCount(2);
});

it('handles unknown error codes gracefully', function () {
    $exception = new SimplePayApiException([99999]);

    expect($exception->getMessage())->toBe('[99999] Ismeretlen hibakód');
    expect($exception->getResolvedCodes())->toHaveCount(0);
});
