<?php

namespace Netipar\SimplePay\Enums;

enum BackEvent: string
{
    case Success = 'SUCCESS';
    case Fail = 'FAIL';
    case Cancel = 'CANCEL';
    case Timeout = 'TIMEOUT';
}
