<?php

declare(strict_types=1);

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Support\Facades\Http;
use Netipar\SimplePay\Support\Signature;
use Netipar\SimplePay\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

/**
 * @param  array<string, mixed>  $data
 */
function fakeSimplePayResponse(array $data, string $secretKey): PromiseInterface
{
    $json = json_encode($data);
    $signature = Signature::generate($secretKey, $json);

    return Http::response($data, 200, [
        'Signature' => $signature,
    ]);
}
