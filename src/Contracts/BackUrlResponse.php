<?php

namespace Netipar\SimplePay\Contracts;

use Illuminate\Http\Request;
use Netipar\SimplePay\Dto\BackResponse;
use Symfony\Component\HttpFoundation\Response;

interface BackUrlResponse
{
    public function toResponse(Request $request, BackResponse $back): Response;
}
