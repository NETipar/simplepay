<?php

declare(strict_types=1);

namespace Netipar\SimplePay\Enums;

enum PaymentMethod: string
{
    case CARD = 'CARD';
    case WIRE = 'WIRE';
    case EAM = 'EAM';
}
