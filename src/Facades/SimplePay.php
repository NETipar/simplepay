<?php

namespace Netipar\SimplePay\Facades;

use Illuminate\Support\Facades\Facade;
use Netipar\SimplePay\Services\AutoPaymentService;
use Netipar\SimplePay\Services\CardStorageService;
use Netipar\SimplePay\Services\PaymentService;
use Netipar\SimplePay\Services\RtpService;

/**
 * @method static PaymentService payment()
 * @method static CardStorageService cardStorage()
 * @method static AutoPaymentService autoPayment()
 * @method static RtpService rtp()
 *
 * @see \Netipar\SimplePay\SimplePay
 */
class SimplePay extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Netipar\SimplePay\SimplePay::class;
    }
}
