<?php

namespace Netipar\SimplePay;

use Netipar\SimplePay\Services\AutoPaymentService;
use Netipar\SimplePay\Services\CardStorageService;
use Netipar\SimplePay\Services\PaymentService;
use Netipar\SimplePay\Services\RtpService;

readonly class SimplePay
{
    public function __construct(
        private PaymentService $paymentService,
        private CardStorageService $cardStorageService,
        private AutoPaymentService $autoPaymentService,
        private RtpService $rtpService,
    ) {}

    public function payment(): PaymentService
    {
        return $this->paymentService;
    }

    public function cardStorage(): CardStorageService
    {
        return $this->cardStorageService;
    }

    public function autoPayment(): AutoPaymentService
    {
        return $this->autoPaymentService;
    }

    public function rtp(): RtpService
    {
        return $this->rtpService;
    }
}
