<?php

namespace Netipar\SimplePay\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Netipar\SimplePay\Dto\IpnMessage;
use Netipar\SimplePay\Enums\Currency;
use Netipar\SimplePay\Enums\PaymentStatus;
use Netipar\SimplePay\Events\IpnReceived;
use Netipar\SimplePay\Events\PaymentAuthorized;
use Netipar\SimplePay\Events\PaymentCancelled;
use Netipar\SimplePay\Events\PaymentFailed;
use Netipar\SimplePay\Events\PaymentRefunded;
use Netipar\SimplePay\Events\PaymentSucceeded;
use Netipar\SimplePay\Events\PaymentTimedOut;
use Netipar\SimplePay\Support\MerchantResolver;
use Netipar\SimplePay\Support\Signature;

class SimplePayWebhookController
{
    public function __invoke(Request $request, MerchantResolver $merchantResolver): JsonResponse
    {
        $body = $request->getContent();
        $data = json_decode($body, true);

        $ipn = IpnMessage::fromArray($data);

        IpnReceived::dispatch($ipn);

        $this->dispatchStatusEvent($ipn);

        $secretKey = isset($data['currency'])
            ? $merchantResolver->getSecretKey(Currency::from($data['currency']))
            : $merchantResolver->getSecretKeyByMerchant($data['merchant']);

        $responseData = $data;
        $responseData['receiveDate'] = date('c');
        $responseJson = json_encode($responseData);

        $signature = Signature::generate($secretKey, $responseJson);

        return response()->json($responseData, 200, [
            'Signature' => $signature,
        ]);
    }

    private function dispatchStatusEvent(IpnMessage $ipn): void
    {
        match ($ipn->status) {
            PaymentStatus::Finished => PaymentSucceeded::dispatch($ipn),
            PaymentStatus::Authorized => PaymentAuthorized::dispatch($ipn),
            PaymentStatus::NotAuthorized => PaymentFailed::dispatch($ipn),
            PaymentStatus::Cancelled => PaymentCancelled::dispatch($ipn),
            PaymentStatus::Timeout => PaymentTimedOut::dispatch($ipn),
            PaymentStatus::Refund => PaymentRefunded::dispatch($ipn),
            default => null,
        };
    }
}
