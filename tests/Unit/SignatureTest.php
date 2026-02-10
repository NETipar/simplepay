<?php

use Netipar\SimplePay\Support\Signature;

it('generates a valid HMAC SHA-384 signature', function () {
    $key = 'test-secret-key';
    $data = '{"orderRef":"ORD-001"}';

    $signature = Signature::generate($key, $data);

    expect($signature)->toBeString();

    $decoded = base64_decode($signature, true);
    expect($decoded)->not->toBeFalse();
    expect(strlen($decoded))->toBe(48); // SHA-384 = 384 bits = 48 bytes
});

it('verifies a valid signature', function () {
    $key = 'test-secret-key';
    $data = '{"orderRef":"ORD-001"}';

    $signature = Signature::generate($key, $data);

    expect(Signature::verify($key, $data, $signature))->toBeTrue();
});

it('rejects an invalid signature', function () {
    $key = 'test-secret-key';
    $data = '{"orderRef":"ORD-001"}';

    expect(Signature::verify($key, $data, 'invalid-signature'))->toBeFalse();
});

it('rejects a signature with wrong key', function () {
    $key = 'test-secret-key';
    $data = '{"orderRef":"ORD-001"}';

    $signature = Signature::generate($key, $data);

    expect(Signature::verify('wrong-key', $data, $signature))->toBeFalse();
});

it('rejects a signature with tampered data', function () {
    $key = 'test-secret-key';
    $data = '{"orderRef":"ORD-001"}';

    $signature = Signature::generate($key, $data);

    expect(Signature::verify($key, '{"orderRef":"ORD-002"}', $signature))->toBeFalse();
});
