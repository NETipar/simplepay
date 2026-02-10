<?php

namespace Netipar\SimplePay\Http\Controllers;

use Illuminate\Http\Request;
use Netipar\SimplePay\Contracts\BackUrlResponse;
use Netipar\SimplePay\Facades\SimplePay;
use Symfony\Component\HttpFoundation\Response;

class SimplePayBackController
{
    public function __invoke(Request $request, BackUrlResponse $response): Response
    {
        $back = SimplePay::payment()->handleBack(
            $request->query('r'),
            $request->query('s'),
        );

        return $response->toResponse($request, $back);
    }
}
