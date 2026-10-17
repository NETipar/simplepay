<?php

declare(strict_types=1);

namespace Netipar\SimplePay\Enums;

enum TransactionType: string
{
    case Cit = 'CIT';
    case Mit = 'MIT';
    case Rec = 'REC';
}
