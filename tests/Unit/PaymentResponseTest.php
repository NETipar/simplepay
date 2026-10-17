<?php

declare(strict_types=1);

use Netipar\SimplePay\Dto\PaymentResponse;

it('accepts a numeric string transaction id', function () {
    $response = PaymentResponse::fromArray([
        'transactionId' => '502272810',
        'paymentUrl' => 'https://sandbox.simplepay.hu/pay/rtp/pspHU/abc',
        'timeout' => '2026-10-17T12:00:00+02:00',
    ]);

    expect($response->transactionId)->toBe(502272810);
});
