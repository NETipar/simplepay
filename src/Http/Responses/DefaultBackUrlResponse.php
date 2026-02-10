<?php

namespace Netipar\SimplePay\Http\Responses;

use Illuminate\Http\Request;
use Netipar\SimplePay\Contracts\BackUrlResponse;
use Netipar\SimplePay\Dto\BackResponse;
use Symfony\Component\HttpFoundation\Response;

class DefaultBackUrlResponse implements BackUrlResponse
{
    public function toResponse(Request $request, BackResponse $back): Response
    {
        return redirect('/');
    }
}
