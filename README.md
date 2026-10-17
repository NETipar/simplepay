
<picture>
  <source media="(prefers-color-scheme: dark)" srcset="art/banner.svg">
  <img alt="laravel-simplepay" src="art/banner.svg">
</picture>

# SimplePay for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/netipar/simplepay.svg?style=flat-square)](https://packagist.org/packages/netipar/simplepay)
[![Tests](https://img.shields.io/github/actions/workflow/status/NETipar/simplepay/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/NETipar/simplepay/actions/workflows/tests.yml?query=branch%3Amain)
[![PHP Version](https://img.shields.io/packagist/php-v/netipar/simplepay.svg?style=flat-square)](https://packagist.org/packages/netipar/simplepay)
[![Total Downloads](https://img.shields.io/packagist/dt/netipar/simplepay.svg?style=flat-square)](https://packagist.org/packages/netipar/simplepay)

Modern Laravel package for [SimplePay](https://simplepay.hu) online payment integration. Start payments, handle IPN webhooks, manage card storage, process refunds, and more.

Based on the official [SimplePay PHP SDK](https://simplepay.hu/fejlesztoknek/) v2.1, rebuilt for Laravel with typed DTOs, Enums, Events, and full API coverage.

## Quick Example

```php
use Netipar\SimplePay\Facades\SimplePay;
use Netipar\SimplePay\Enums\Currency;
use Netipar\SimplePay\Enums\PaymentMethod;
use Netipar\SimplePay\Dto\PaymentRequest;
use Netipar\SimplePay\Dto\Address;

$response = SimplePay::payment()->start(new PaymentRequest(
    currency: Currency::HUF,
    total: 2500,
    orderRef: 'ORDER-123',
    customerEmail: 'customer@example.com',
    language: 'HU',
    url: route('simplepay.back'),
    methods: [PaymentMethod::CARD],
    invoice: new Address(
        name: 'John Doe',
        country: 'hu',
        city: 'Budapest',
        zip: '1111',
        address: 'Main Street 1',
    ),
));

return redirect($response->paymentUrl);
```

## Requirements

- PHP 8.3+
- Laravel 12 or 13
- A [SimplePay](https://simplepay.hu) merchant account

## Installation

```bash
composer require netipar/simplepay
```

Publish the config file:

```bash
php artisan vendor:publish --tag=simplepay-config
```

Or publish the config file and the logo assets at once:

```bash
php artisan vendor:publish --tag=simplepay
```

Add your merchant credentials to `.env`:

```
SIMPLEPAY_SANDBOX=true
SIMPLEPAY_HUF_MERCHANT=your_merchant_id
SIMPLEPAY_HUF_SECRET_KEY=your_secret_key
SIMPLEPAY_BACK_URL=https://yourdomain.com/payment/back
```

## Usage

### Obtaining the Services

Access all services through the `SimplePay` facade:

```php
use Netipar\SimplePay\Facades\SimplePay;

SimplePay::payment();      // PaymentService
SimplePay::cardStorage();  // CardStorageService
SimplePay::autoPayment();  // AutoPaymentService
SimplePay::rtp();          // RtpService
```

### Start a Payment

```php
use Netipar\SimplePay\Facades\SimplePay;
use Netipar\SimplePay\Enums\Currency;
use Netipar\SimplePay\Enums\PaymentMethod;
use Netipar\SimplePay\Dto\PaymentRequest;
use Netipar\SimplePay\Dto\Address;
use Netipar\SimplePay\Dto\Item;

$response = SimplePay::payment()->start(new PaymentRequest(
    currency: Currency::HUF,
    total: 25400,
    orderRef: 'ORDER-2026-001',
    customerEmail: 'customer@example.com',
    language: 'HU',
    url: route('simplepay.back'),
    methods: [PaymentMethod::CARD],
    invoice: new Address(
        name: 'Customer Kft.',
        country: 'hu',
        city: 'Budapest',
        zip: '1234',
        address: 'Main Street 1.',
    ),
    items: [
        new Item(
            title: 'Product name',
            price: 10000,
            quantity: 2,
        ),
        new Item(
            title: 'Delivery',
            price: 5400,
            quantity: 1,
        ),
    ],
));

$response->transactionId; // SimplePay transaction ID
$response->paymentUrl;    // Redirect URL to SimplePay payment page
$response->timeout;       // Payment timeout

return redirect($response->paymentUrl);
```

### Event-Specific Redirect URLs

SimplePay returns the customer to `url` after every outcome. Pass `urls` to use a separate page per outcome; SimplePay ignores `url` when `urls` is sent:

```php
use Netipar\SimplePay\Dto\RedirectUrls;

$response = SimplePay::payment()->start(new PaymentRequest(
    // ... same as above
    urls: new RedirectUrls(
        success: route('payment.success'),
        fail: route('payment.failed'),
        cancel: route('checkout'),
        timeout: route('payment.timeout'),
    ),
));
```

The order reference of a failed transaction can be sent again until the first successful payment; every start creates a new SimplePay transaction ID.

### Two-Step Payment (Authorize + Finish)

```php
use Netipar\SimplePay\Dto\PaymentRequest;
use Netipar\SimplePay\Dto\FinishRequest;

// Step 1: Authorize with twoStep enabled
$response = SimplePay::payment()->start(new PaymentRequest(
    // ... same as above
    twoStep: true,
));

// Step 2: Later, finish the authorized payment
$result = SimplePay::payment()->finish(new FinishRequest(
    currency: Currency::HUF,
    transactionId: $transactionId,
    orderRef: 'ORDER-2026-001',
    approveTotal: 25400,
));
```

### Refund a Transaction

```php
use Netipar\SimplePay\Dto\RefundRequest;

// Full refund
$result = SimplePay::payment()->refund(new RefundRequest(
    currency: Currency::HUF,
    transactionId: $transactionId,
    orderRef: 'ORDER-2026-001',
    refundTotal: 25400,
));

// Partial refund
$result = SimplePay::payment()->refund(new RefundRequest(
    currency: Currency::HUF,
    transactionId: $transactionId,
    orderRef: 'ORDER-2026-001',
    refundTotal: 10000,
));
```

### Query Transaction Status

```php
$result = SimplePay::payment()->query(
    currency: Currency::HUF,
    transactionId: $transactionId,
);

$result->status;         // PaymentStatus enum
$result->remainingTotal; // Remaining amount
$result->paymentDate;    // Payment date
$result->finishDate;     // Finish date (two-step)
```

### Cancel a Transaction

```php
SimplePay::payment()->cancel(
    currency: Currency::HUF,
    transactionId: $transactionId,
);
```

### Card Storage (OneClick Payments)

```php
use Netipar\SimplePay\Dto\CardStorageDoRequest;

// Pay with a stored card
$result = SimplePay::cardStorage()->do(new CardStorageDoRequest(
    currency: Currency::HUF,
    transactionId: null, // deprecated: not sent, removed in the next major version
    cardId: $savedCardId,
    orderRef: 'ORDER-2026-002',
    total: 5000,
    customerEmail: 'customer@example.com',
    url: route('simplepay.back'),
    cardSecret: $cardSecret,
    threeDSReqAuthMethod: '02',
    browser: $browserData, // the customer's browser data for the 3DS check
));

// Query stored card
$card = SimplePay::cardStorage()->cardQuery(Currency::HUF, $cardId);

// Remove stored card
SimplePay::cardStorage()->cardCancel(Currency::HUF, $cardId);
```

### Recurring Payments

The start of a card registration (`recurring` in `PaymentRequest`) returns the recurring tokens in `$response->tokens`; they become active when the IPN reports the successful payment.

```php
use Netipar\SimplePay\Dto\RecurringRequest;
use Netipar\SimplePay\Enums\TransactionType;

$result = SimplePay::cardStorage()->doRecurring(new RecurringRequest(
    currency: Currency::HUF,
    transactionId: null, // deprecated: not sent, removed in the next major version
    orderRef: 'SUB-2026-003',
    total: 9900,
    customerEmail: 'customer@example.com',
    url: route('simplepay.back'),
    type: TransactionType::Mit,
    token: $recurringToken,
));
```

### Apple Pay

```php
use Netipar\SimplePay\Dto\ApplePayStartRequest;
use Netipar\SimplePay\Dto\ApplePayDoRequest;

// Start Apple Pay session (returns the API response with applePaySession).
// Pass `domain` when the checkout does not run on the host of APP_URL.
$session = SimplePay::payment()->startApplePay(new ApplePayStartRequest(
    currency: Currency::HUF,
    total: 5000,
    orderRef: 'ORDER-AP-001',
    customerEmail: 'customer@example.com',
    url: route('simplepay.back'),
    domain: 'shop.example.com', // the FQDN registered for Apple Pay; defaults to the host of app.url
));

// Complete Apple Pay payment with the token from Apple Pay JS
$result = SimplePay::payment()->doApplePay(new ApplePayDoRequest(
    currency: Currency::HUF,
    transactionId: $session['transactionId'],
    orderRef: 'ORDER-AP-001',
    token: $tokenFromJs,
));
```

### Google Pay on Your Checkout Page

Google Pay is also available on the SimplePay payment page with `PaymentMethod::CARD`. To show the Google Pay button on your own checkout page, render it with `googlePayConfig()` and charge the token from your backend:

```php
use Netipar\SimplePay\Dto\GooglePayDoRequest;

// Checkout page: the values of the Google Pay button
$googlePay = SimplePay::payment()->googlePayConfig(Currency::HUF, 5000);

// Backend endpoint called by the button with the Base64 encoded Google Pay token
$validated = $request->validate(['googlePayToken' => ['required', 'string']]);

$response = SimplePay::payment()->doGooglePay(new GooglePayDoRequest(
    currency: Currency::HUF,
    total: 5000,
    orderRef: 'ORDER-GP-001',
    customerEmail: 'customer@example.com',
    googlePayToken: $validated['googlePayToken'],
    url: route('simplepay.back'), // where SimplePay returns the customer after a 3DS challenge
));

if ($response->requiresChallenge()) {
    // Send the browser to $response->redirectUrl
}
```

Set `SIMPLEPAY_GOOGLE_PAY_MERCHANT_ID` and `SIMPLEPAY_GOOGLE_PAY_MERCHANT_NAME` from the Google Pay & Wallet Console. The full flow with the JavaScript button is in the [Google Pay example](examples/en/google-pay.md).

### Auto Payment (Card Data on Your Page)

PCI-DSS certified merchants can send the card data themselves:

```php
use Netipar\SimplePay\Dto\AutoPaymentRequest;
use Netipar\SimplePay\Dto\CardData;
use Netipar\SimplePay\Enums\TransactionType;

$result = SimplePay::autoPayment()->auto(new AutoPaymentRequest(
    currency: Currency::HUF,
    total: 2500,
    orderRef: 'ORDER-AUTO-001',
    customerEmail: 'customer@example.com',
    cardData: new CardData(cardNumber: $number, expiry: '0130', cvc: $cvc, cardHolder: 'John Doe'),
    type: TransactionType::Cit,
    browserData: $browserData,       // the customer's browser data for the 3DS check
    url: route('simplepay.back'),    // where SimplePay returns the customer after a 3DS challenge
    threeDSReqAuthMethod: '02',
));
```

See [3DS browser data](examples/en/card-storage.md#3ds-browser-data) for building `BrowserData`.

### EAM / Qvik Payment

```php
use Netipar\SimplePay\Dto\EamStartRequest;

$response = SimplePay::payment()->startEam(new EamStartRequest(
    currency: Currency::HUF,
    total: 3000,
    orderRef: 'ORDER-EAM-001',
    customerEmail: 'customer@example.com',
    url: route('simplepay.back'),
    eamType: 1, // 1 QR, 2 deeplink, 3 NFC, 4-7 combinations
));
```

### Request to Pay (RTP)

```php
use Netipar\SimplePay\Dto\RtpStartRequest;

// Start RTP transaction
$result = SimplePay::rtp()->start(new RtpStartRequest(
    currency: Currency::HUF,
    total: 15000,
    orderRef: 'RTP-2026-001',
    customerEmail: 'customer@example.com',
    url: route('simplepay.back'),
    customer: 'Customer Name',
));

// Query RTP status
$status = SimplePay::rtp()->query(Currency::HUF, transactionId: $id);

// Refund RTP (refund total, then the original total)
SimplePay::rtp()->refund(Currency::HUF, $transactionId, 15000, originalTotal: 15000);

// Reverse RTP
SimplePay::rtp()->reverse(Currency::HUF, $transactionId);
```

Request to Pay back redirects can carry `BackEvent::Pending`: the customer gave the bank account number, but the transfer has not arrived yet. `RtpStartRequest` accepts `urls` with a `pending` URL as well.

## IPN Webhook

The package automatically registers a `POST /simplepay/ipn` route with signature verification middleware. Listen to payment events in your application:

```php
// In EventServiceProvider or via Event::listen()
use Netipar\SimplePay\Events\PaymentSucceeded;
use Netipar\SimplePay\Events\PaymentFailed;

protected $listen = [
    PaymentSucceeded::class => [HandlePaymentSuccess::class],
    PaymentFailed::class => [HandlePaymentFailure::class],
];
```

Available events (all receive `IpnMessage` DTO):

| Event | Trigger |
|---|---|
| `IpnReceived` | Every IPN notification |
| `PaymentSucceeded` | FINISHED status |
| `PaymentAuthorized` | AUTHORIZED (two-step) |
| `PaymentFailed` | NOTAUTHORIZED |
| `PaymentCancelled` | CANCELLED |
| `PaymentTimedOut` | TIMEOUT |
| `PaymentRefunded` | REFUND |

The `IpnMessage` DTO contains:

```php
$event->ipnMessage->orderRef;       // Your order reference
$event->ipnMessage->transactionId;  // SimplePay transaction ID
$event->ipnMessage->status;         // PaymentStatus enum
$event->ipnMessage->paymentMethod;  // PaymentMethod enum
$event->ipnMessage->cardMask;       // Masked card number
```

## Back URL Handling

The package registers a `GET /simplepay/back` route. Customize the response by binding your own `BackUrlResponse` in `AppServiceProvider`:

```php
use Netipar\SimplePay\Contracts\BackUrlResponse;
use App\Http\Responses\SimplePayBackResponse;

// AppServiceProvider::register()
$this->app->singleton(BackUrlResponse::class, SimplePayBackResponse::class);
```

```php
use Netipar\SimplePay\Contracts\BackUrlResponse;
use Netipar\SimplePay\Dto\BackResponse;
use Netipar\SimplePay\Enums\BackEvent;

class SimplePayBackResponse implements BackUrlResponse
{
    public function toResponse(Request $request, BackResponse $back): Response
    {
        return match ($back->event) {
            BackEvent::Success => redirect()->route('payment.success', $back->orderRef),
            BackEvent::Fail    => redirect()->route('payment.failed'),
            BackEvent::Cancel  => redirect()->route('checkout'),
            BackEvent::Timeout => redirect()->route('payment.timeout'),
            BackEvent::Pending => redirect()->route('payment.pending'), // Request to Pay only
        };
    }
}
```

## Enums

Always use enum cases instead of raw strings:

- **`Currency`** -- `HUF`, `EUR`, `USD`
- **`PaymentMethod`** -- `CARD`, `WIRE`, `EAM`
- **`PaymentStatus`** -- `INIT`, `FINISHED`, `AUTHORIZED`, `NOTAUTHORIZED`, `INPAYMENT`, `CANCELLED`, `TIMEOUT`, `INFRAUD`, `FRAUD`, `REFUND`, `REVERSED`
- **`BackEvent`** -- `SUCCESS`, `FAIL`, `CANCEL`, `TIMEOUT`, `PENDING` (Request to Pay)
- **`TransactionType`** -- `CIT` (Customer Initiated), `MIT` (Merchant Initiated), `REC` (Recurring)
- **`ErrorCode`** -- 358+ error codes with Hungarian descriptions

## Error Handling

The package throws `SimplePayApiException` when the SimplePay API returns error codes:

```php
use Netipar\SimplePay\Exceptions\SimplePayApiException;

try {
    $response = SimplePay::payment()->start($request);
} catch (SimplePayApiException $e) {
    $e->getMessage();       // "[5083] Token times szükséges"
    $e->getErrorCodes();    // [5083]
    $e->hasErrorCode(5083); // true
    $e->getResolvedCodes(); // [ErrorCode::TokenTimesRequired]
    $e->getResponseData();  // The full API response body
}
```

When a card storage or auto payment requires a 3DS challenge, the API answers with error code `3004`, and the response body holds the `redirectUrl` where the customer has to be sent:

```php
use Netipar\SimplePay\Enums\ErrorCode;

try {
    $result = SimplePay::cardStorage()->do($request);
} catch (SimplePayApiException $e) {
    if ($e->hasErrorCode(ErrorCode::ThreeDsChallengeRedirect->value)) {
        return redirect($e->getResponseData()['redirectUrl']);
    }

    throw $e;
}
```

## Configuration

Key `.env` variables:

```
SIMPLEPAY_SANDBOX=true
SIMPLEPAY_HUF_MERCHANT=your_merchant_id
SIMPLEPAY_HUF_SECRET_KEY=your_secret_key
SIMPLEPAY_BACK_URL=https://yourdomain.com/payment/back
SIMPLEPAY_TIMEOUT=600
SIMPLEPAY_LOG_CHANNEL=simplepay
SIMPLEPAY_AUTO_CHALLENGE=true
SIMPLEPAY_GOOGLE_PAY_MERCHANT_ID=your_google_pay_merchant_id
SIMPLEPAY_GOOGLE_PAY_MERCHANT_NAME="Your Shop"
```

Multi-currency support -- each currency has its own merchant credentials:

```
SIMPLEPAY_EUR_MERCHANT=your_eur_merchant_id
SIMPLEPAY_EUR_SECRET_KEY=your_eur_secret_key
SIMPLEPAY_USD_MERCHANT=your_usd_merchant_id
SIMPLEPAY_USD_SECRET_KEY=your_usd_secret_key
```

## Logos and Assets

The package includes the official SimplePay merchant logo pack. Publish the assets:

```bash
php artisan vendor:publish --tag=simplepay-assets
```

This copies logos and pre-composed variations to `public/vendor/simplepay/`.

Usage in Blade, Vue.js, or React:

```blade
{{-- Blade --}}
<img src="{{ asset('vendor/simplepay/logos/simplepay_200x50.png') }}" alt="SimplePay">
```

```vue
<!-- Vue.js / React (Inertia.js) -->
<img src="/vendor/simplepay/logos/simplepay_200x50.png" alt="SimplePay" />
```

Available logos: SimplePay (multiple sizes/orientations + white variants, and an SVG of the vertical logo), Visa, Mastercard, Maestro, Amex, Apple Pay, Google Pay, Qvik, OTP SZÉP. Pre-composed card type combinations are also included. See [examples](examples/en/logos-and-assets.md) for Blade, Vue.js, and React checkout page examples.

## Logging

Set `SIMPLEPAY_LOG_CHANNEL` to any Laravel log channel name. Example dedicated channel in `config/logging.php`:

```php
'simplepay' => [
    'driver' => 'daily',
    'path' => storage_path('logs/simplepay.log'),
    'days' => 14,
],
```

## Services Overview

| Service | Access | Purpose |
|---|---|---|
| `PaymentService` | `SimplePay::payment()` | Standard payments, refunds, queries, Apple Pay, Google Pay, EAM |
| `CardStorageService` | `SimplePay::cardStorage()` | OneClick, recurring, card/token management |
| `AutoPaymentService` | `SimplePay::autoPayment()` | PCI-DSS direct card payments |
| `RtpService` | `SimplePay::rtp()` | Request to Pay (bank transfers) |

## SDK Compatibility

This package is built based on the **SimplePay PHP SDK v2.1** (API v2). All API requests include the `sdkVersion: SimplePay_PHP_SDK_2.1_Laravel` identifier.

The merchant-side Google Pay flow follows the Payment PHP SDK 2.1.5 (2026-06-27) and the SimplePay Google Pay documentation (2026-09-01); the `urls` and `pending` handling follows the Request to Pay documentation (2026-08-17).

## Examples

- [English examples](examples/en/)
- [Magyar példák](examples/hu/)

## Testing

```bash
composer test        # PHPStan, Pint check, type coverage and the Pest suite
composer test:unit   # Pest suite only
composer analyse     # PHPStan (Larastan, level 7)
composer lint        # Fix code style with Pint
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please review the [contributing guide](.github/CONTRIBUTING.md) before opening a pull request.

## Security Vulnerabilities

Please review the [security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## Credits

- [NETipar](https://netipar.hu)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
