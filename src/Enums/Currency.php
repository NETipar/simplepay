<?php

declare(strict_types=1);

namespace Netipar\SimplePay\Enums;

enum Currency: string
{
    case HUF = 'HUF';
    case EUR = 'EUR';
    case USD = 'USD';
}
