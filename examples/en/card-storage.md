# Card Storage (OneClick & Recurring)

SimplePay allows you to store a customer's card for future payments. This enables OneClick payments (customer-initiated with stored card) and Recurring payments (merchant-initiated automatic charges).

## Prerequisites

To use card storage, the initial payment must include the `recurring` parameter. See [Starting a Payment](payment-start.md#card-storage-oneclick--recurring) for setup details.

## OneClick Payment (Customer-Initiated)

Charge a stored card for a customer-initiated transaction.

```php
use Netipar\SimplePay\Facades\SimplePay;
use Netipar\SimplePay\Enums\Currency;
use Netipar\SimplePay\Dto\CardStorageDoRequest;

$result = SimplePay::cardStorage()->do(new CardStorageDoRequest(
    currency: Currency::HUF,
    transactionId: null, // deprecated: not sent, removed in the next major version
    cardId: 'card-abc-123',
    orderRef: 'ORDER-456',
    total: 3000,
    customerEmail: 'customer@example.com',
    url: route('simplepay.back'), // where SimplePay returns the customer after a 3DS challenge
    cardSecret: $cardSecret,      // the secret the customer gave when the card was stored
    threeDSReqAuthMethod: '02',   // 01 guest, 02 registered, 05 third-party login
    browser: $browser,            // see "3DS Browser Data" below
));
```

### 3DS Browser Data

OneClick and auto payments send the customer's browser data to the card issuer's 3DS check. Collect `navigator.language`, `screen.colorDepth`, `screen.height`, `screen.width` and `new Date().getTimezoneOffset()` on the checkout page, and add the request headers and IP address on the server:

```php
use Netipar\SimplePay\Dto\BrowserData;

$browser = new BrowserData(
    accept: (string) $request->header('Accept'),
    agent: (string) $request->userAgent(),
    language: $request->input('browser.lang'),
    javaEnabled: false,
    colorDepth: (int) $request->input('browser.color'),
    screenHeight: (int) $request->input('browser.height'),
    screenWidth: (int) $request->input('browser.width'),
    timeZone: (int) $request->input('browser.tz'),
    ip: $request->ip(),
);
```

Without browser data the issuer's 3DS check may fail. If the issuer asks for a challenge, the API answers with error code `3004`; send the customer to `$e->getResponseData()['redirectUrl']`.

## Recurring Payment (Merchant-Initiated)

Charge a stored card for a merchant-initiated recurring transaction (e.g., subscriptions, scheduled billing).

```php
use Netipar\SimplePay\Facades\SimplePay;
use Netipar\SimplePay\Enums\Currency;
use Netipar\SimplePay\Enums\TransactionType;
use Netipar\SimplePay\Dto\RecurringRequest;

$result = SimplePay::cardStorage()->doRecurring(new RecurringRequest(
    currency: Currency::HUF,
    transactionId: null, // deprecated: not sent, removed in the next major version
    orderRef: 'ORDER-REC-001',
    total: 5000,
    customerEmail: 'customer@example.com',
    url: route('simplepay.back'),
    type: TransactionType::Mit,
    token: $recurringToken, // required: one of the tokens of the card registration
));
```

A recurring payment without `token` throws an `InvalidArgumentException` before the request is sent.

### Transaction Types

| Type | Description |
|---|---|
| `TransactionType::Mit` | Merchant-Initiated Transaction -- charges initiated by the merchant without customer interaction |
| `TransactionType::Cit` | Customer-Initiated Transaction -- charges initiated by the customer |

## Card Query

Retrieve information about a stored card, optionally including transaction history.

```php
use Netipar\SimplePay\Facades\SimplePay;
use Netipar\SimplePay\Enums\Currency;

// Basic card query
$card = SimplePay::cardStorage()->cardQuery(Currency::HUF, 'card-abc-123');

// Card query with transaction history
$cardWithHistory = SimplePay::cardStorage()->cardQuery(
    Currency::HUF,
    'card-abc-123',
    history: true,
);
```

## Card Cancel

Remove a stored card so it can no longer be used for future payments.

```php
use Netipar\SimplePay\Facades\SimplePay;
use Netipar\SimplePay\Enums\Currency;

SimplePay::cardStorage()->cardCancel(Currency::HUF, 'card-abc-123');
```

## Token Query

Retrieve information about a card storage token.

```php
use Netipar\SimplePay\Facades\SimplePay;
use Netipar\SimplePay\Enums\Currency;

SimplePay::cardStorage()->tokenQuery(Currency::HUF, 'token-xyz');
```

## Token Cancel

Invalidate a card storage token so it can no longer be used.

```php
use Netipar\SimplePay\Facades\SimplePay;
use Netipar\SimplePay\Enums\Currency;

SimplePay::cardStorage()->tokenCancel(Currency::HUF, 'token-xyz');
```

## Typical Recurring Billing Flow

```php
// 1. Initial payment with card storage (customer completes this interactively)
$response = SimplePay::payment()->start(new PaymentRequest(
    currency: Currency::HUF,
    total: 5000,
    orderRef: 'SUB-INITIAL',
    customerEmail: 'customer@example.com',
    language: 'HU',
    url: route('simplepay.back'),
    methods: [PaymentMethod::CARD],
    recurring: [
        'times' => 12,
        'until' => '2026-12-31T00:00:00+02:00',
        'maxAmount' => 60000,
    ],
    invoice: new Address(/* ... */),
    items: [new Item(title: 'Monthly plan', price: 5000, quantity: 1)],
));

// 2. Store the recurring tokens of the registration; they become active when the IPN reports the successful payment
$recurringToken = $response->tokens[0];

// 4. Monthly recurring charge (merchant-initiated, no customer interaction)
$result = SimplePay::cardStorage()->doRecurring(new RecurringRequest(
    currency: Currency::HUF,
    transactionId: null,
    orderRef: 'SUB-MONTH-02',
    total: 5000,
    customerEmail: 'customer@example.com',
    url: route('simplepay.back'),
    type: TransactionType::Mit,
    token: $recurringToken,
));

// 5. When subscription is cancelled, remove the stored card
SimplePay::cardStorage()->cardCancel(Currency::HUF, $cardId);
```
