# Query, Refund & Cancel

Manage existing transactions by querying their status, issuing refunds (partial or full), or cancelling them entirely.

## Query a Transaction

Retrieve the current status and details of a transaction.

```php
use Netipar\SimplePay\Facades\SimplePay;
use Netipar\SimplePay\Enums\Currency;

$query = SimplePay::payment()->query(
    currency: Currency::HUF,
    transactionId: 123456,
);
```

### Query Response Properties

| Property | Type | Description |
|---|---|---|
| `$query->status` | `PaymentStatus` | Current transaction status |
| `$query->total` | `float` | Original transaction total |
| `$query->orderRef` | `string` | Your order reference |
| `$query->remainingTotal` | `?float` | Remaining amount (after partial refunds) |
| `$query->paymentDate` | `?string` | Payment date (ISO 8601) |
| `$query->finishDate` | `?string` | Finish date for two-step payments (ISO 8601) |
| `$query->method` | `?string` | Payment method (e.g. `CARD`) |

## Refund a Transaction

Issue a partial or full refund for a completed transaction.

### Full Refund

```php
use Netipar\SimplePay\Facades\SimplePay;
use Netipar\SimplePay\Enums\Currency;
use Netipar\SimplePay\Dto\RefundRequest;

$refund = SimplePay::payment()->refund(new RefundRequest(
    currency: Currency::HUF,
    transactionId: 123456,
    orderRef: 'ORDER-123',
    amount: 2500, // Full amount
));
```

### Partial Refund

```php
$refund = SimplePay::payment()->refund(new RefundRequest(
    currency: Currency::HUF,
    transactionId: 123456,
    orderRef: 'ORDER-123',
    amount: 1000, // Partial amount
));

// Check remaining total after refund
echo $refund->remainingTotal; // 1500
```

### Refund Response Properties

| Property | Type | Description |
|---|---|---|
| `$refund->remainingTotal` | `float` | Remaining amount after the refund |

## Cancel a Transaction

Cancel a transaction that has not yet been completed.

```php
use Netipar\SimplePay\Facades\SimplePay;
use Netipar\SimplePay\Enums\Currency;

$cancel = SimplePay::payment()->cancel(
    currency: Currency::HUF,
    transactionId: 123456,
);
```

## Finish a Two-Step Payment (Preauthorization Capture)

For transactions started with `twoStep: true`, use `finish()` to capture the authorized amount.

```php
use Netipar\SimplePay\Facades\SimplePay;
use Netipar\SimplePay\Enums\Currency;

// Capture the full authorized amount
$finish = SimplePay::payment()->finish(
    currency: Currency::HUF,
    transactionId: 123456,
    amount: 5000,
    orderRef: 'ORDER-PREAUTH-001',
);
```

You can also capture a partial amount (less than the originally authorized total):

```php
// Capture only a portion of the authorized amount
$finish = SimplePay::payment()->finish(
    currency: Currency::HUF,
    transactionId: 123456,
    amount: 3000, // Less than the original 5000
    orderRef: 'ORDER-PREAUTH-001',
);
```

## Error Handling

API error responses throw a `SimplePayApiException` containing the error code and its human-readable description:

```php
use Netipar\SimplePay\Exceptions\SimplePayApiException;

try {
    $refund = SimplePay::payment()->refund(new RefundRequest(
        currency: Currency::HUF,
        transactionId: 123456,
        orderRef: 'ORDER-123',
        amount: 2500,
    ));
} catch (SimplePayApiException $e) {
    $e->getMessage();       // "[5022] A tranzakció nem a kéréshez elvárt állapotban van"
    $e->getErrorCodes();    // [5022]
    $e->hasErrorCode(5022); // true
    $e->getResolvedCodes(); // [ErrorCode::TransactionInvalidState]
}
```

> **Tip:** `AUTHORIZED` (two-step) transactions cannot be refunded. Use `cancel()` to release the hold, or `finish()` to capture first.

## Typical Workflow

```php
// 1. Start a payment
$response = SimplePay::payment()->start(new PaymentRequest(/* ... */));

// 2. Customer completes payment...

// 3. Later, query the transaction status
$query = SimplePay::payment()->query(
    currency: Currency::HUF,
    transactionId: $response->transactionId,
);

// 4. If needed, issue a partial refund
if ($needsPartialRefund) {
    $refund = SimplePay::payment()->refund(new RefundRequest(
        currency: Currency::HUF,
        transactionId: $response->transactionId,
        orderRef: 'ORDER-123',
        amount: 500,
    ));
}
```
