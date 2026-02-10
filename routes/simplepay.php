<?php

use Illuminate\Support\Facades\Route;
use Netipar\SimplePay\Http\Controllers\SimplePayBackController;
use Netipar\SimplePay\Http\Controllers\SimplePayWebhookController;
use Netipar\SimplePay\Http\Middleware\VerifySimplePaySignature;

Route::post('simplepay/ipn', SimplePayWebhookController::class)
    ->middleware(VerifySimplePaySignature::class)
    ->name('simplepay.ipn');

Route::middleware('web')
    ->get('simplepay/back', SimplePayBackController::class)
    ->name('simplepay.back');
