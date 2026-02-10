<?php

namespace Netipar\SimplePay\Dto;

use Netipar\SimplePay\Enums\Currency;
use Netipar\SimplePay\Enums\PaymentMethod;
use Random\RandomException;

readonly class PaymentRequest
{
    /**
     * @param  array<int, PaymentMethod>  $methods
     * @param  array<int, Item>|null  $items
     */
    public function __construct(
        public Currency $currency,
        public float $total,
        public string $orderRef,
        public string $customerEmail,
        public string $language,
        public string $url,
        public array $methods = [PaymentMethod::CARD],
        public ?Address $invoice = null,
        public ?Address $delivery = null,
        public ?array $items = null,
        public ?int $timeout = null,
        public ?bool $twoStep = null,
        public ?string $cardSecret = null,
        public ?array $recurring = null,
        public ?string $token = null,
        public ?string $sdkToken = null,
        public ?string $threeDSReqAuthMethod = null,
        public ?float $shippingPrice = null,
        public ?float $discount = null,
        public ?string $customer = null,
    ) {}

    /**
     * @throws RandomException
     */
    public function toArray(): array
    {
        $data = [
            'salt' => bin2hex(random_bytes(16)),
            'merchant' => '',
            'orderRef' => $this->orderRef,
            'currency' => $this->currency->value,
            'customerEmail' => $this->customerEmail,
            'language' => $this->language,
            'sdkVersion' => 'SimplePay_PHP_SDK_2.1_Laravel',
            'methods' => array_map(fn (PaymentMethod $m) => $m->value, $this->methods),
            'total' => $this->total,
            'timeout' => date('c', time() + ($this->timeout ?? config('simplepay.timeout', 600))),
            'url' => $this->url,
        ];

        if ($this->invoice !== null) {
            $data['invoice'] = $this->invoice->toArray();
        }

        if ($this->delivery !== null) {
            $data['delivery'] = $this->delivery->toArray();
        }

        if ($this->items !== null) {
            $data['items'] = array_map(fn (Item $item) => $item->toArray(), $this->items);
        }

        if ($this->twoStep !== null) {
            $data['twoStep'] = $this->twoStep;
        }

        if ($this->cardSecret !== null) {
            $data['cardSecret'] = $this->cardSecret;
        }

        if ($this->recurring !== null) {
            $data['recurring'] = $this->recurring;
        }

        if ($this->token !== null) {
            $data['token'] = $this->token;
        }

        if ($this->sdkToken !== null) {
            $data['sdkToken'] = $this->sdkToken;
        }

        if ($this->threeDSReqAuthMethod !== null) {
            $data['threeDSReqAuthMethod'] = $this->threeDSReqAuthMethod;
        }

        if ($this->shippingPrice !== null) {
            $data['shippingPrice'] = $this->shippingPrice;
        }

        if ($this->discount !== null) {
            $data['discount'] = $this->discount;
        }

        if ($this->customer !== null) {
            $data['customer'] = $this->customer;
        }

        return $data;
    }
}
