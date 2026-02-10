<?php

namespace Netipar\SimplePay\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Netipar\SimplePay\Dto\IpnMessage;

readonly class PaymentCancelled
{
    use Dispatchable;

    public function __construct(
        public IpnMessage $ipn,
    ) {}
}
