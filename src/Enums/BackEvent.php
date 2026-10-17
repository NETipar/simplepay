<?php

declare(strict_types=1);

namespace Netipar\SimplePay\Enums;

enum BackEvent: string
{
    case Success = 'SUCCESS';
    case Fail = 'FAIL';
    case Cancel = 'CANCEL';
    case Timeout = 'TIMEOUT';

    /**
     * Request to Pay only: the customer gave the bank account number, the transfer has not arrived yet.
     */
    case Pending = 'PENDING';
}
