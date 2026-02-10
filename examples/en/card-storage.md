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
    transactionId: 123456,
    cardId: 'card-abc-123',
    orderRef: 'ORDER-456',
    total: 3000,
    customerEmail: 'customer@example.com',
    url: route('simplepay.back'),
));
```

## Recurring Payment (Merchant-Initiated)

Charge a stored card for a merchant-initiated recurring transaction (e.g., subscriptions, scheduled billing).

```php
use Netipar\SimplePay\Facades\SimplePay;
use Netipar\SimplePay\Enums\Currency;
use Netipar\SimplePay\Enums\TransactionType;
use Netipar\SimplePay\Dto\RecurringRequest;

$result = SimplePay::cardStorage()->doRecurring(new RecurringRequest(
    currency: Currency::HUF,
    transactionId: 789012,
    orderRef: 'ORDER-REC-001',
    total: 5000,
    customerEmail: 'customer@example.com',
    url: route('simplepay.back'),
    type: TransactionType::Mit,
));
```

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

// 2. Customer completes payment, card is stored...
// 3. IPN webhook receives cardId in the response...

// 4. Monthly recurring charge (merchant-initiated, no customer interaction)
$result = SimplePay::cardStorage()->doRecurring(new RecurringRequest(
    currency: Currency::HUF,
    transactionId: $originalTransactionId,
    orderRef: 'SUB-MONTH-02',
    total: 5000,
    customerEmail: 'customer@example.com',
    url: route('simplepay.back'),
    type: TransactionType::Mit,
));

// 5. When subscription is cancelled, remove the stored card
SimplePay::cardStorage()->cardCancel(Currency::HUF, $cardId);
```
