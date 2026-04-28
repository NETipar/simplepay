<?php

use Illuminate\Support\Facades\Event;
use Illuminate\Testing\TestResponse;
use Netipar\SimplePay\Events\IpnReceived;
use Netipar\SimplePay\Events\PaymentAuthorized;
use Netipar\SimplePay\Events\PaymentCancelled;
use Netipar\SimplePay\Events\PaymentFailed;
use Netipar\SimplePay\Events\PaymentRefunded;
use Netipar\SimplePay\Events\PaymentSucceeded;
use Netipar\SimplePay\Events\PaymentTimedOut;
use Netipar\SimplePay\Support\Signature;

function buildIpnPayload(string $status = 'FINISHED', string $currency = 'HUF'): array
{
    return [
        'orderRef' => 'ORD-001',
        'transactionId' => 12345,
        'status' => $status,
        'currency' => $currency,
        'total' => 1500,
        'finishDate' => '2026-02-10T12:00:00+01:00',
        'merchant' => 'TEST_HUF_MERCHANT',
    ];
}

function postIpn(array $payload, string $secretKey = 'TEST_HUF_SECRET_KEY'): TestResponse
{
    $json = json_encode($payload);
    $signature = Signature::generate($secretKey, $json);

    return test()->postJson('/simplepay/ipn', $payload, [
        'Signature' => $signature,
    ]);
}

it('accepts a valid IPN and dispatches IpnReceived event', function () {
    Event::fake();

    $response = postIpn(buildIpnPayload());

    $response->assertOk();
    $response->assertHeader('Signature');
    $response->assertJsonStructure(['receiveDate', 'orderRef', 'transactionId', 'status']);

    Event::assertDispatched(IpnReceived::class, function ($event) {
        return $event->ipn->orderRef === 'ORD-001'
            && $event->ipn->transactionId === 12345;
    });
});

it('dispatches PaymentSucceeded for FINISHED status', function () {
    Event::fake();

    postIpn(buildIpnPayload('FINISHED'));

    Event::assertDispatched(IpnReceived::class);
    Event::assertDispatched(PaymentSucceeded::class);
});

it('dispatches PaymentAuthorized for AUTHORIZED status', function () {
    Event::fake();

    postIpn(buildIpnPayload('AUTHORIZED'));

    Event::assertDispatched(IpnReceived::class);
    Event::assertDispatched(PaymentAuthorized::class);
});

it('dispatches PaymentFailed for NOTAUTHORIZED status', function () {
    Event::fake();

    postIpn(buildIpnPayload('NOTAUTHORIZED'));

    Event::assertDispatched(IpnReceived::class);
    Event::assertDispatched(PaymentFailed::class);
});

it('dispatches PaymentCancelled for CANCELLED status', function () {
    Event::fake();

    postIpn(buildIpnPayload('CANCELLED'));

    Event::assertDispatched(IpnReceived::class);
    Event::assertDispatched(PaymentCancelled::class);
});

it('dispatches PaymentTimedOut for TIMEOUT status', function () {
    Event::fake();

    postIpn(buildIpnPayload('TIMEOUT'));

    Event::assertDispatched(IpnReceived::class);
    Event::assertDispatched(PaymentTimedOut::class);
});

it('dispatches PaymentRefunded for REFUND status', function () {
    Event::fake();

    postIpn(buildIpnPayload('REFUND'));

    Event::assertDispatched(IpnReceived::class);
    Event::assertDispatched(PaymentRefunded::class);
});

it('rejects request with missing signature', function () {
    $payload = buildIpnPayload();

    $response = test()->postJson('/simplepay/ipn', $payload);

    $response->assertStatus(401);
});

it('rejects request with invalid signature', function () {
    $payload = buildIpnPayload();
    $json = json_encode($payload);

    $response = test()->postJson('/simplepay/ipn', $payload, [
        'Signature' => 'invalid-signature',
    ]);

    $response->assertStatus(401);
});

it('accepts an IPN without currency and total, resolving secret key by merchant', function () {
    Event::fake();

    $payload = [
        'salt' => 'abc123',
        'orderRef' => 'ORD-002',
        'method' => 'CARD',
        'merchant' => 'TEST_HUF_MERCHANT',
        'finishDate' => '2026-02-10T12:00:00+01:00',
        'paymentDate' => '2026-02-10T12:00:00+01:00',
        'transactionId' => 67890,
        'status' => 'FINISHED',
    ];

    $response = postIpn($payload);

    $response->assertOk();
    $response->assertHeader('Signature');

    Event::assertDispatched(IpnReceived::class, function ($event) {
        return $event->ipn->orderRef === 'ORD-002'
            && $event->ipn->transactionId === 67890
            && $event->ipn->currency === null
            && $event->ipn->total === null
            && $event->ipn->merchant === 'TEST_HUF_MERCHANT';
    });
});

it('returns response with receiveDate and signature header', function () {
    Event::fake();

    $response = postIpn(buildIpnPayload());

    $response->assertOk();
    $responseData = $response->json();

    expect($responseData)->toHaveKey('receiveDate');
    expect($response->headers->get('Signature'))->not->toBeNull();

    $secretKey = 'TEST_HUF_SECRET_KEY';
    $responseBody = $response->getContent();
    $signature = $response->headers->get('Signature');

    expect(Signature::verify($secretKey, $responseBody, $signature))->toBeTrue();
});
