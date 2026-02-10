<?php

namespace Netipar\SimplePay\Enums;

enum PaymentStatus: string
{
    case Init = 'INIT';
    case Finished = 'FINISHED';
    case Authorized = 'AUTHORIZED';
    case NotAuthorized = 'NOTAUTHORIZED';
    case InPayment = 'INPAYMENT';
    case Cancelled = 'CANCELLED';
    case Timeout = 'TIMEOUT';
    case InFraud = 'INFRAUD';
    case Fraud = 'FRAUD';
    case Refund = 'REFUND';
    case Reversed = 'REVERSED';
}
