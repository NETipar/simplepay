<?php

use Netipar\SimplePay\Dto\BackResponse;
use Netipar\SimplePay\Enums\BackEvent;
use Netipar\SimplePay\Support\Signature;

it('parses a valid back response', function () {
    $data = json_encode([
        'e' => 'SUCCESS',
        't' => 12345,
        'o' => 'ORD-001',
        'm' => 'TEST_HUF_MERCHANT',
    ]);

    $r = base64_encode($data);
    $s = Signature::generate('TEST_HUF_SECRET_KEY', $data);

    $response = new BackResponse($r, $s);

    expect($response->event)->toBe(BackEvent::Success);
    expect($response->transactionId)->toBe(12345);
    expect($response->orderRef)->toBe('ORD-001');
    expect($response->merchant)->toBe('TEST_HUF_MERCHANT');
});

it('throws on invalid base64', function () {
    new BackResponse('not-valid-base64!!!', 'fake-signature');
})->throws(RuntimeException::class);

it('throws on invalid json', function () {
    $r = base64_encode('not-json');
    new BackResponse($r, 'fake-signature');
})->throws(RuntimeException::class);

it('throws on invalid signature', function () {
    $data = json_encode([
        'e' => 'SUCCESS',
        't' => 12345,
        'o' => 'ORD-001',
        'm' => 'TEST_HUF_MERCHANT',
    ]);

    $r = base64_encode($data);

    new BackResponse($r, 'wrong-signature');
})->throws(RuntimeException::class, 'Invalid signature in back response');

it('parses a fail event', function () {
    $data = json_encode([
        'e' => 'FAIL',
        't' => 99999,
        'o' => 'ORD-FAIL',
        'm' => 'TEST_EUR_MERCHANT',
    ]);

    $r = base64_encode($data);
    $s = Signature::generate('TEST_EUR_SECRET_KEY', $data);

    $response = new BackResponse($r, $s);

    expect($response->event)->toBe(BackEvent::Fail);
    expect($response->transactionId)->toBe(99999);
    expect($response->orderRef)->toBe('ORD-FAIL');
});

it('parses a cancel event', function () {
    $data = json_encode([
        'e' => 'CANCEL',
        't' => 55555,
        'o' => 'ORD-CANCEL',
        'm' => 'TEST_HUF_MERCHANT',
    ]);

    $r = base64_encode($data);
    $s = Signature::generate('TEST_HUF_SECRET_KEY', $data);

    $response = new BackResponse($r, $s);

    expect($response->event)->toBe(BackEvent::Cancel);
});

it('parses a timeout event', function () {
    $data = json_encode([
        'e' => 'TIMEOUT',
        't' => 77777,
        'o' => 'ORD-TIMEOUT',
        'm' => 'TEST_HUF_MERCHANT',
    ]);

    $r = base64_encode($data);
    $s = Signature::generate('TEST_HUF_SECRET_KEY', $data);

    $response = new BackResponse($r, $s);

    expect($response->event)->toBe(BackEvent::Timeout);
});
