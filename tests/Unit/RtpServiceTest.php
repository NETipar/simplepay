<?php

use Illuminate\Support\Facades\Http;
use Netipar\SimplePay\Dto\PaymentResponse;
use Netipar\SimplePay\Dto\RtpDoRequest;
use Netipar\SimplePay\Dto\RtpPayment;
use Netipar\SimplePay\Dto\RtpStartRequest;
use Netipar\SimplePay\Enums\Currency;
use Netipar\SimplePay\Services\RtpService;

beforeEach(function () {
    Http::fake([
        '*/rtp/start' => Http::response([
            'transactionId' => 123456,
            'paymentUrl' => 'https://sandbox.simplepay.hu/pay/123456',
            'timeout' => '2025-01-01T12:00:00+01:00',
        ], 200, ['Signature' => '']),
        '*/rtp/do' => Http::response([
            'resultCode' => 0,
            'resultMessage' => 'OK',
        ], 200, ['Signature' => '']),
        '*/rtp/query' => Http::response([
            'transactionId' => '123456',
            'status' => 'FINISHED',
        ], 200, ['Signature' => '']),
        '*/rtp/refund' => Http::response([
            'transactionId' => '123456',
            'refundStatus' => 'REFUNDED',
        ], 200, ['Signature' => '']),
        '*/rtp/reverse' => Http::response([
            'transactionId' => '123456',
            'reverseStatus' => 'REVERSED',
        ], 200, ['Signature' => '']),
    ]);

    $this->rtpService = app(RtpService::class);
});

it('sends start request to /rtp/start and returns PaymentResponse', function () {
    $request = new RtpStartRequest(
        currency: Currency::HUF,
        total: 1000.0,
        orderRef: 'ORDER-001',
        customerEmail: 'test@example.com',
        url: 'https://example.com/rtp/back',
        customer: 'Test Customer',
    );

    $response = $this->rtpService->start($request);

    expect($response)->toBeInstanceOf(PaymentResponse::class)
        ->and($response->transactionId)->toBe(123456)
        ->and($response->paymentUrl)->toBe('https://sandbox.simplepay.hu/pay/123456')
        ->and($response->timeout)->toBe('2025-01-01T12:00:00+01:00');

    Http::assertSent(function ($httpRequest) {
        return str_contains($httpRequest->url(), '/rtp/start')
            && $httpRequest['merchant'] === 'TEST_HUF_MERCHANT'
            && $httpRequest['orderRef'] === 'ORDER-001'
            && $httpRequest['currency'] === 'HUF'
            && (float) $httpRequest['total'] === 1000.0
            && $httpRequest['customerEmail'] === 'test@example.com'
            && $httpRequest['customer'] === 'Test Customer';
    });
});

it('sends do request to /rtp/do with payments array', function () {
    $request = new RtpDoRequest(
        currency: Currency::HUF,
        payments: [
            new RtpPayment(
                orderRef: 'ORDER-001',
                total: 500.0,
                customerEmail: 'customer1@example.com',
                customer: 'Customer One',
            ),
            new RtpPayment(
                orderRef: 'ORDER-002',
                total: 750.0,
                customerEmail: 'customer2@example.com',
                customer: 'Customer Two',
            ),
        ],
    );

    $response = $this->rtpService->do($request);

    expect($response)->toBeArray()
        ->and($response['resultCode'])->toBe(0);

    Http::assertSent(function ($httpRequest) {
        return str_contains($httpRequest->url(), '/rtp/do')
            && $httpRequest['merchant'] === 'TEST_HUF_MERCHANT'
            && $httpRequest['currency'] === 'HUF'
            && count($httpRequest['payments']) === 2
            && $httpRequest['payments'][0]['orderRef'] === 'ORDER-001'
            && $httpRequest['payments'][1]['orderRef'] === 'ORDER-002';
    });
});

it('sends query request to /rtp/query with transactionId', function () {
    $response = $this->rtpService->query(Currency::HUF, transactionId: '123456');

    expect($response)->toBeArray()
        ->and($response['transactionId'])->toBe('123456');

    Http::assertSent(function ($httpRequest) {
        return str_contains($httpRequest->url(), '/rtp/query')
            && $httpRequest['merchant'] === 'TEST_HUF_MERCHANT'
            && $httpRequest['transactionId'] === '123456';
    });
});

it('sends query request to /rtp/query with orderRef', function () {
    $response = $this->rtpService->query(Currency::HUF, orderRef: 'ORDER-001');

    expect($response)->toBeArray();

    Http::assertSent(function ($httpRequest) {
        return str_contains($httpRequest->url(), '/rtp/query')
            && $httpRequest['merchant'] === 'TEST_HUF_MERCHANT'
            && $httpRequest['orderRef'] === 'ORDER-001';
    });
});

it('sends refund request to /rtp/refund with amount', function () {
    $response = $this->rtpService->refund(Currency::HUF, '123456', 500.0);

    expect($response)->toBeArray()
        ->and($response['refundStatus'])->toBe('REFUNDED');

    Http::assertSent(function ($httpRequest) {
        return str_contains($httpRequest->url(), '/rtp/refund')
            && $httpRequest['merchant'] === 'TEST_HUF_MERCHANT'
            && $httpRequest['transactionId'] === '123456'
            && (float) $httpRequest['amount'] === 500.0;
    });
});

it('sends reverse request to /rtp/reverse', function () {
    $response = $this->rtpService->reverse(Currency::HUF, '123456');

    expect($response)->toBeArray()
        ->and($response['reverseStatus'])->toBe('REVERSED');

    Http::assertSent(function ($httpRequest) {
        return str_contains($httpRequest->url(), '/rtp/reverse')
            && $httpRequest['merchant'] === 'TEST_HUF_MERCHANT'
            && $httpRequest['transactionId'] === '123456';
    });
});
