---
name: simplepay-development
description: Build and integrate SimplePay online payment features using the netipar/simplepay Laravel package, including payment start, IPN webhook handling, back redirects, refunds, two-step payments, card storage (OneClick), recurring payments, auto payments, and Request to Pay (RTP) flows.
---

# SimplePay Payment Integration Development

## When to use this skill

Use this skill when:
- Starting online payments and redirecting users to the SimplePay payment page
- Handling IPN (Instant Payment Notification) webhooks
- Processing back redirects after payment completion
- Implementing refunds, cancellations, or transaction queries
- Implementing two-step (authorize + capture) payment flows
- Storing cards for OneClick payments or recurring billing
- Implementing auto (PCI-DSS) payments with direct card data
- Implementing Request to Pay (RTP) bank transfer flows
- Configuring the simplepay package (sandbox, multi-currency, logging)

## Package overview

The `netipar/simplepay` package wraps the SimplePay PHP SDK v2.1 for Laravel. It provides typed DTOs, enums, events, and signature verification.

- Namespace: `Netipar\SimplePay\`
- Facade: `Netipar\SimplePay\Facades\SimplePay`
- Config: `config/simplepay.php`
- Routes: `POST /simplepay/ipn`, `GET /simplepay/back` (auto-registered)

## Service access

Access services via the facade:

```php
use Netipar\SimplePay\Facades\SimplePay;

SimplePay::payment()      // PaymentService
SimplePay::cardStorage()  // CardStorageService
SimplePay::autoPayment()  // AutoPaymentService
SimplePay::rtp()          // RtpService
```

## Start a payment

```php
use Netipar\SimplePay\Dto\PaymentRequest;
use Netipar\SimplePay\Dto\Item;
use Netipar\SimplePay\Dto\Address;
use Netipar\SimplePay\Enums\Currency;
use Netipar\SimplePay\Enums\PaymentMethod;
use Netipar\SimplePay\Facades\SimplePay;

$request = new PaymentRequest(
    currency: Currency::HUF,
    customerEmail: 'customer@example.com',
    orderRef: 'ORDER-001',
    items: [
        new Item(title: 'Product name', price: 1000, quantity: 1),
        new Item(title: 'Service', price: 2500, quantity: 2, tax: '27'),
    ],
    methods: [PaymentMethod::CARD],
    url: route('payment.back'),
    invoice: new Address(
        name: 'Customer Kft.',
        country: 'HU',
        city: 'Budapest',
        zip: '1234',
        address: 'Main Street 1.',
    ),
);

$response = SimplePay::payment()->start($request);

// $response->transactionId  (int)
// $response->paymentUrl     (string) — redirect user here
// $response->timeout        (string)

return redirect($response->paymentUrl);
```

## Two-step payment (authorize + capture)

```php
use Netipar\SimplePay\Dto\PaymentRequest;
use Netipar\SimplePay\Dto\FinishRequest;

// Step 1: authorize
$request = new PaymentRequest(
    currency: Currency::HUF,
    customerEmail: 'customer@example.com',
    orderRef: 'ORDER-002',
    items: [new Item(title: 'Product', price: 5000, quantity: 1)],
    methods: [PaymentMethod::CARD],
    url: route('payment.back'),
    twoStep: true,
);
$response = SimplePay::payment()->start($request);

// Step 2: capture (later)
$finish = new FinishRequest(
    currency: Currency::HUF,
    transactionId: $response->transactionId,
    originalTotal: 5000,
    approveTotal: 5000,
);
$finishResponse = SimplePay::payment()->finish($finish);
// $finishResponse->transactionId, $finishResponse->total
```

## IPN webhook handling

Routes auto-registered: `POST /simplepay/ipn` with `VerifySimplePaySignature` middleware.

Listen to dispatched events:

```php
use Netipar\SimplePay\Events\PaymentSucceeded;
use Netipar\SimplePay\Events\PaymentFailed;
use Netipar\SimplePay\Events\PaymentCancelled;
use Netipar\SimplePay\Events\PaymentTimedOut;
use Netipar\SimplePay\Events\PaymentAuthorized;
use Netipar\SimplePay\Events\PaymentRefunded;
use Netipar\SimplePay\Events\IpnReceived;

// In EventServiceProvider or as listener
PaymentSucceeded::class => [HandleSuccessfulPayment::class],
PaymentFailed::class => [HandleFailedPayment::class],
```

All events carry an `ipn` property of type `Netipar\SimplePay\Dto\IpnMessage`:

```php
class HandleSuccessfulPayment
{
    public function handle(PaymentSucceeded $event): void
    {
        $ipn = $event->ipn;
        $ipn->orderRef;       // string
        $ipn->transactionId;  // int
        $ipn->status;         // PaymentStatus enum
        $ipn->paymentMethod;  // PaymentMethod enum
        $ipn->finishDate;     // ?string
    }
}
```

## Available events

| Event | Dispatched when |
|---|---|
| `IpnReceived` | Every IPN webhook |
| `PaymentSucceeded` | Status = FINISHED |
| `PaymentAuthorized` | Status = AUTHORIZED |
| `PaymentFailed` | Status = NOTAUTHORIZED |
| `PaymentCancelled` | Status = CANCELLED |
| `PaymentTimedOut` | Status = TIMEOUT |
| `PaymentRefunded` | Status = REFUND |

## Handle back redirect

The `GET /simplepay/back` route is auto-registered. Customize the response by implementing `BackUrlResponse`:

```php
use Netipar\SimplePay\Contracts\BackUrlResponse;
use Netipar\SimplePay\Dto\BackResponse;

class CustomBackResponse implements BackUrlResponse
{
    public function toResponse(BackResponse $response): mixed
    {
        // $response->event  (BackEvent enum: SUCCESS, FAIL, CANCEL, TIMEOUT)
        // $response->transactionId
        // $response->orderRef

        return redirect()->route('payment.result', [
            'status' => $response->event->value,
        ]);
    }
}

// Register in AppServiceProvider
$this->app->singleton(BackUrlResponse::class, CustomBackResponse::class);
```

## Refund a payment

```php
use Netipar\SimplePay\Dto\RefundRequest;

$request = new RefundRequest(
    currency: Currency::HUF,
    transactionId: 123456789,
    refundTotal: 1000,
);

$response = SimplePay::payment()->refund($request);
// $response->transactionId, $response->remainingTotal
```

## Query a transaction

```php
$response = SimplePay::payment()->query(
    currency: Currency::HUF,
    transactionId: 123456789,
);

// $response->transactionId
// $response->status     (PaymentStatus enum)
// $response->total
// $response->orderRef
// $response->merchant
// $response->remainingTotal
```

## Cancel a transaction

```php
SimplePay::payment()->cancel(
    currency: Currency::HUF,
    transactionId: 123456789,
);
```

## Card storage (OneClick) payment

```php
use Netipar\SimplePay\Dto\CardStorageDoRequest;

$request = new CardStorageDoRequest(
    currency: Currency::HUF,
    customerEmail: 'customer@example.com',
    orderRef: 'ORDER-003',
    items: [new Item(title: 'Product', price: 1000, quantity: 1)],
    url: route('payment.back'),
    cardId: 'stored-card-id',
);

$result = SimplePay::cardStorage()->do($request);
```

## Recurring payment

```php
use Netipar\SimplePay\Dto\RecurringRequest;

$request = new RecurringRequest(
    currency: Currency::HUF,
    customerEmail: 'customer@example.com',
    orderRef: 'ORDER-004',
    items: [new Item(title: 'Subscription', price: 2990, quantity: 1)],
    url: route('payment.back'),
    token: 'recurring-token',
);

$result = SimplePay::cardStorage()->doRecurring($request);
```

## Card query and cancel

```php
// Query stored card
$result = SimplePay::cardStorage()->cardQuery(
    currency: Currency::HUF,
    cardId: 'stored-card-id',
    history: true,
);

// Cancel stored card
SimplePay::cardStorage()->cardCancel(Currency::HUF, 'stored-card-id');

// Token management
SimplePay::cardStorage()->tokenQuery(Currency::HUF, 'token');
SimplePay::cardStorage()->tokenCancel(Currency::HUF, 'token');
```

## Request to Pay (RTP) — bank transfer

```php
use Netipar\SimplePay\Dto\RtpStartRequest;
use Netipar\SimplePay\Dto\RtpDoRequest;
use Netipar\SimplePay\Dto\RtpPayment;

// Start RTP session
$request = new RtpStartRequest(
    currency: Currency::HUF,
    customerEmail: 'customer@example.com',
    orderRef: 'ORDER-005',
    items: [new Item(title: 'Product', price: 5000, quantity: 1)],
    url: route('payment.back'),
);
$response = SimplePay::rtp()->start($request);

// Execute RTP payment
$doRequest = new RtpDoRequest(
    currency: Currency::HUF,
    transactionId: $response->transactionId,
    payments: [
        new RtpPayment(
            bankCode: 'BANK_CODE',
            accountNumber: 'HU12345678901234567890123456',
        ),
    ],
);
$result = SimplePay::rtp()->do($doRequest);

// Query/refund/reverse RTP
SimplePay::rtp()->query(Currency::HUF, transactionId: '12345');
SimplePay::rtp()->refund(Currency::HUF, transactionId: '12345', amount: 5000);
SimplePay::rtp()->reverse(Currency::HUF, transactionId: '12345');
```

## Available enums

Always use enum cases instead of raw strings:

- `Currency`: `HUF`, `EUR`, `USD`
- `PaymentMethod`: `CARD`, `WIRE`, `EAM`
- `PaymentStatus`: `INIT`, `FINISHED`, `AUTHORIZED`, `NOTAUTHORIZED`, `INPAYMENT`, `CANCELLED`, `TIMEOUT`, `INFRAUD`, `FRAUD`, `REFUND`, `REVERSED`
- `BackEvent`: `SUCCESS`, `FAIL`, `CANCEL`, `TIMEOUT`
- `TransactionType`: `CIT` (Customer-Initiated), `MIT` (Merchant-Initiated), `REC` (Recurring)
- `ErrorCode`: 350+ error codes with `description()` method returning Hungarian text

## Error handling

```php
use Netipar\SimplePay\Exceptions\SimplePayApiException;

try {
    $response = SimplePay::payment()->start($request);
} catch (SimplePayApiException $e) {
    $e->getMessage();        // "[5083] Token times szükséges"
    $e->getErrorCodes();     // [5083]
    $e->hasErrorCode(5083);  // true
    $e->getResolvedCodes();  // [ErrorCode::TokenTimesRequired]
}
```

## Configuration

Key `.env` variables:

```
SIMPLEPAY_SANDBOX=true
SIMPLEPAY_HUF_MERCHANT=merchant_id
SIMPLEPAY_HUF_SECRET_KEY=secret_key
SIMPLEPAY_EUR_MERCHANT=eur_merchant_id
SIMPLEPAY_EUR_SECRET_KEY=eur_secret_key
SIMPLEPAY_BACK_URL=https://yourdomain.com/payment/back
SIMPLEPAY_TIMEOUT=600
SIMPLEPAY_LOG_CHANNEL=simplepay
SIMPLEPAY_AUTO_CHALLENGE=true
```

Multi-currency: configure separate merchant credentials per currency (HUF, EUR, USD).

Publish config with: `php artisan vendor:publish --tag=simplepay-config`

## Logo and asset publishing

Publish SimplePay logos and card brand images to `public/vendor/simplepay/`:

```bash
php artisan vendor:publish --tag=simplepay-assets
```

Published structure:

```
public/vendor/simplepay/
├── logos/                          # Individual logos
│   ├── simplepay_horizontal_01.png # SimplePay horizontal (recommended)
│   ├── simplepay_horizontal_01-white.png
│   ├── simplepay_horizontal_02.png
│   ├── simplepay_horizontal_02-white.png
│   ├── simplepay_vertical.png
│   ├── simplepay_vertical-wite.png
│   ├── simplepay_top.png
│   ├── simplepay_top-white.png
│   ├── simplepay_200x50.png
│   ├── simplepay_w140.png / simplepay_w140-white.png
│   ├── simplepay_w240.png / simplepay_w240-white.png
│   ├── simplepay_w360.png / simplepay_w360-white.png
│   ├── visa_logo_color.png / visa_logo_bw.png
│   ├── mastercard_logo_color_new.png / mastercard_logo_bw_new.png
│   ├── maestro_logo_color_new.png / maestro_logo_bw_new.png
│   ├── amex_logo_color.png / amex_logo_bw.png
│   ├── Apple_Pay_Mark_RGB.png
│   ├── GPay_logo_color_new.png
│   ├── OTPszepkartya_logo_color.png
│   └── qvik_logo_color.png
└── variaciok/                      # Pre-composed logo combinations
    ├── SimplePay_Logo_wo_Amex/     # SimplePay + Visa/MC/Maestro (no Amex)
    ├── SimplePay_Logo_with_Amex/   # SimplePay + Visa/MC/Maestro/Amex
    ├── SimplePay_Logo_wo_Amex+Qvik/
    ├── SimplePay_Logo_with_Amex+Qvik/
    ├── SimplePay_Logo_kartyak_wo_Amex/
    ├── SimplePay_Logo-kartyak_with_Amex/
    ├── SimplePay_Logo+MC-Maestro-Visa/
    ├── SimplePay_Logo+Qvik/
    └── SimplePay_Logo+SZEP/
```

Use in Blade templates:

```html
<img src="{{ asset('vendor/simplepay/logos/simplepay_horizontal_01.png') }}" alt="SimplePay">
```

Each variation directory contains layout options: `_left`, `_top_horizontal`, `_top_vertical`, `_vertical`, with `-hu` Hungarian-labeled versions.

## DTOs overview

All DTOs use readonly properties. Request DTOs include a `toArray()` method that generates a random salt and adds SDK version info.

| DTO | Purpose |
|---|---|
| `PaymentRequest` | Start a payment with items, addresses, methods |
| `PaymentResponse` | Transaction ID, payment URL, timeout |
| `FinishRequest` / `FinishResponse` | Two-step capture |
| `RefundRequest` / `RefundResponse` | Refund with remaining total |
| `QueryResponse` | Full transaction status |
| `BackResponse` | Parsed back redirect (base64-decoded, signature-verified) |
| `IpnMessage` | Webhook payload with payment status |
| `Item` | Product: title, price, quantity, tax |
| `Address` | Name, country, city, zip, address |
| `CardStorageDoRequest` | OneClick payment with stored card |
| `RecurringRequest` | Recurring payment with token |
| `AutoPaymentRequest` | Direct card payment (PCI-DSS) |
| `RtpStartRequest` / `RtpDoRequest` | Request to Pay flows |
| `RtpPayment` | Bank transfer payment details |
| `CardData` | Card number, expiry, CVC |
| `BrowserData` | Browser fingerprint for 3DS |
