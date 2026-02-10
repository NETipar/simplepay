# Configuration

## Publish the Configuration File

```bash
php artisan vendor:publish --tag=simplepay-config
```

This publishes the configuration file to `config/simplepay.php`.

## Environment Variables

Add the following variables to your `.env` file:

```env
SIMPLEPAY_SANDBOX=true
SIMPLEPAY_HUF_MERCHANT=your_huf_merchant_id
SIMPLEPAY_HUF_SECRET_KEY=your_huf_secret_key
SIMPLEPAY_EUR_MERCHANT=your_eur_merchant_id
SIMPLEPAY_EUR_SECRET_KEY=your_eur_secret_key
SIMPLEPAY_BACK_URL=https://yourdomain.com/payment/back
SIMPLEPAY_LOG_CHANNEL=simplepay
SIMPLEPAY_TIMEOUT=600
```

## Configuration Structure

```php
// config/simplepay.php

return [
    /*
    |--------------------------------------------------------------------------
    | Sandbox Mode
    |--------------------------------------------------------------------------
    |
    | When enabled, the package uses the SimplePay sandbox API for testing.
    | Set to false in production.
    |
    */
    'sandbox' => env('SIMPLEPAY_SANDBOX', true),

    /*
    |--------------------------------------------------------------------------
    | Merchants per Currency
    |--------------------------------------------------------------------------
    |
    | Each currency requires its own merchant ID and secret key.
    | Configure only the currencies you need.
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
    | API URLs
    |--------------------------------------------------------------------------
    |
    | The package automatically selects the correct API URL based on the
    | sandbox setting. You typically don't need to change these.
    |
    */
    'api_url' => env('SIMPLEPAY_API_URL', 'https://secure.simplepay.hu/payment/v2'),
    'sandbox_api_url' => env('SIMPLEPAY_SANDBOX_API_URL', 'https://sandbox.simplepay.hu/payment/v2'),

    /*
    |--------------------------------------------------------------------------
    | Back URL
    |--------------------------------------------------------------------------
    |
    | Default URL where customers are redirected after payment.
    | Can be overridden per transaction in the PaymentRequest.
    |
    */
    'back_url' => env('SIMPLEPAY_BACK_URL'),

    /*
    |--------------------------------------------------------------------------
    | Timeout
    |--------------------------------------------------------------------------
    |
    | Payment timeout in seconds. After this time, the payment session expires.
    | Default: 600 seconds (10 minutes).
    |
    */
    'timeout' => env('SIMPLEPAY_TIMEOUT', 600),

    /*
    |--------------------------------------------------------------------------
    | Log Channel
    |--------------------------------------------------------------------------
    |
    | The log channel used for SimplePay-related logging.
    | Set to null to use the default log channel.
    |
    */
    'log_channel' => env('SIMPLEPAY_LOG_CHANNEL'),

    /*
    |--------------------------------------------------------------------------
    | Auto Challenge
    |--------------------------------------------------------------------------
    |
    | When enabled, the package automatically handles 3DS challenge flows.
    |
    */
    'auto_challenge' => env('SIMPLEPAY_AUTO_CHALLENGE', true),
];
```

## Merchants per Currency

SimplePay requires separate merchant credentials for each currency. Configure only the currencies your application supports:

- **HUF** -- Hungarian Forint
- **EUR** -- Euro
- **USD** -- US Dollar

Each currency requires a `merchant` ID and a `secret_key` provided by SimplePay.

## Sandbox Mode

When `sandbox` is set to `true`, all API calls are sent to the SimplePay sandbox environment. This is useful for development and testing without processing real payments.

Set `SIMPLEPAY_SANDBOX=false` in your `.env` when deploying to production.

## Dedicated Log Channel

To keep SimplePay logs separate from your application logs, set up a dedicated log channel:

```php
// config/logging.php

'channels' => [
    // ... other channels

    'simplepay' => [
        'driver' => 'daily',
        'path' => storage_path('logs/simplepay.log'),
        'level' => 'debug',
        'days' => 30,
    ],
],
```

Then set the environment variable:

```env
SIMPLEPAY_LOG_CHANNEL=simplepay
```

This writes all SimplePay-related log entries (API requests, IPN notifications, errors) to `storage/logs/simplepay.log`.

## Timeout

The `timeout` value (in seconds) controls how long a payment session remains valid. After this period, if the customer has not completed the payment, the session expires and a `Timeout` event is triggered.

The default value is 600 seconds (10 minutes).

## Auto Challenge

When `auto_challenge` is enabled, the package automatically handles 3D Secure challenge flows during card payments. This is enabled by default and is recommended for most use cases.
