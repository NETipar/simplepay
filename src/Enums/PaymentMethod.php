<?php

namespace Netipar\SimplePay\Enums;

enum PaymentMethod: string
{
    case CARD = 'CARD';
    case WIRE = 'WIRE';
    case EAM = 'EAM';
}
