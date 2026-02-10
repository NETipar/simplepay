# Request to Pay (RTP)

Request to Pay (RTP) is a payment initiation method where the merchant sends a payment request to the customer, who then approves and completes the payment. It supports single and batch payment requests.

## Start an RTP Payment

Initiate a single Request to Pay transaction.

```php
use Netipar\SimplePay\Facades\SimplePay;
use Netipar\SimplePay\Enums\Currency;
use Netipar\SimplePay\Dto\RtpStartRequest;

$response = SimplePay::rtp()->start(new RtpStartRequest(
    currency: Currency::HUF,
    total: 5000,
    orderRef: 'RTP-ORDER-001',
    customerEmail: 'customer@example.com',
    url: route('simplepay.back'),
    customer: 'Test Customer',
));
```

## Batch RTP

Send multiple payment requests at once using the batch RTP method.

```php
use Netipar\SimplePay\Facades\SimplePay;
use Netipar\SimplePay\Enums\Currency;
use Netipar\SimplePay\Dto\RtpDoRequest;
use Netipar\SimplePay\Dto\RtpPayment;

SimplePay::rtp()->do(new RtpDoRequest(
    currency: Currency::HUF,
    payments: [
        new RtpPayment(
            orderRef: 'RTP-001',
            total: 1000,
            customerEmail: 'a@example.com',
            customer: 'Customer A',
        ),
        new RtpPayment(
            orderRef: 'RTP-002',
            total: 2000,
            customerEmail: 'b@example.com',
            customer: 'Customer B',
        ),
        new RtpPayment(
            orderRef: 'RTP-003',
            total: 3500,
            customerEmail: 'c@example.com',
            customer: 'Customer C',
        ),
    ],
));
```

## Query an RTP Transaction

Check the status of an RTP transaction.

```php
use Netipar\SimplePay\Facades\SimplePay;
use Netipar\SimplePay\Enums\Currency;

$query = SimplePay::rtp()->query(Currency::HUF, transactionId: '12345');
```

## Refund an RTP Transaction

Issue a refund for a completed RTP payment.

```php
use Netipar\SimplePay\Facades\SimplePay;
use Netipar\SimplePay\Enums\Currency;

// Partial refund
SimplePay::rtp()->refund(Currency::HUF, '12345', 1000);

// Full refund
SimplePay::rtp()->refund(Currency::HUF, '12345', 5000);
```

## Reverse an RTP Transaction

Reverse an RTP transaction that has not yet been completed.

```php
use Netipar\SimplePay\Facades\SimplePay;
use Netipar\SimplePay\Enums\Currency;

SimplePay::rtp()->reverse(Currency::HUF, '12345');
```

## Typical RTP Flow

```
1. Merchant calls SimplePay::rtp()->start() or SimplePay::rtp()->do()
2. SimplePay sends a payment request notification to the customer
3. Customer opens the payment request and approves it
4. Payment is processed
5. IPN webhook notifies your application of the result
6. If needed, refund or reverse the transaction
```
