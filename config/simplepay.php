<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Merchant Configuration
    |--------------------------------------------------------------------------
    |
    | Configure merchant credentials per currency. Each currency needs its own
    | merchant ID and secret key from SimplePay.
    |
    */

    'merchants' => [
        'HUF' => [
            'merchant' => env('SIMPLEPAY_HUF_MERCHANT'),
            'secret_key' => env('SIMPLEPAY_HUF_SECRET_KEY'),
        ],
        'EUR' => [
            'merchant' => env('SIMPLEPAY_EUR_MERCHANT'),
            'secret_key' => env('SIMPLEPAY_EUR_SECRET_KEY'),
        ],
        'USD' => [
            'merchant' => env('SIMPLEPAY_USD_MERCHANT'),
            'secret_key' => env('SIMPLEPAY_USD_SECRET_KEY'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Sandbox Mode
    |--------------------------------------------------------------------------
    |
    | When enabled, all API calls go to the SimplePay sandbox environment.
    |
    */

    'sandbox' => env('SIMPLEPAY_SANDBOX', true),

    /*
    |--------------------------------------------------------------------------
    | API Base URLs
    |--------------------------------------------------------------------------
    |
    | Base URLs for the SimplePay API endpoints. These are pre-configured with
    | the official SimplePay URLs, but can be overridden if needed.
    |
    */

    'api' => [
        'live' => 'https://secure.simplepay.hu/payment',
        'sandbox' => 'https://sandbox.simplepay.hu/payment',
        'live_securepay' => 'https://securepay.simplepay.hu',
        'sandbox_securepay' => 'https://sandbox.simplepay.hu',
    ],

    /*
    |--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------
    |
    | back: The URL where SimplePay redirects the customer after payment.
    | ipn: The route name for the IPN webhook endpoint.
    |
    */

    'urls' => [
        'back' => env('SIMPLEPAY_BACK_URL'),
        'ipn' => 'simplepay.ipn',
    ],

    /*
    |--------------------------------------------------------------------------
    | Timeout
    |--------------------------------------------------------------------------
    |
    | Default payment timeout in seconds. After this period, the payment
    | transaction will expire if not completed.
    |
    */

    'timeout' => env('SIMPLEPAY_TIMEOUT', 600),

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Configure logging for SimplePay API requests and responses.
    | Set 'channel' to a log channel name from config/logging.php,
    | or null to disable logging. You can use a dedicated channel
    | like 'simplepay' with a daily driver for easy debugging.
    |
    | Example logging.php channel:
    |   'simplepay' => [
    |       'driver' => 'daily',
    |       'path' => storage_path('logs/simplepay.log'),
    |       'days' => 14,
    |   ],
    |
    */

    'log_channel' => env('SIMPLEPAY_LOG_CHANNEL'),

    /*
    |--------------------------------------------------------------------------
    | Auto Challenge
    |--------------------------------------------------------------------------
    |
    | Automatically handle 3DS challenge flow for AutoPayment transactions.
    |
    */

    'auto_challenge' => env('SIMPLEPAY_AUTO_CHALLENGE', true),

];
