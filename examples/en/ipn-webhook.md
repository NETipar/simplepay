# IPN Webhook Handling

SimplePay sends Instant Payment Notifications (IPN) to your application when payment status changes occur. This package automatically registers a webhook route and dispatches Laravel events that you can listen to.

## Automatic Route

The package automatically registers the following route:

```
POST /simplepay/ipn
```

No manual route registration is required. The package handles signature verification and response formatting automatically.

## Listening to Events

Use standard Laravel event listeners to react to payment status changes.

### Register Listeners

```php
// app/Providers/EventServiceProvider.php

use Netipar\SimplePay\Events\PaymentSucceeded;
use Netipar\SimplePay\Events\PaymentFailed;
use Netipar\SimplePay\Events\PaymentCancelled;
use Netipar\SimplePay\Events\PaymentTimedOut;
use Netipar\SimplePay\Events\PaymentRefunded;
use Netipar\SimplePay\Events\PaymentAuthorized;
use App\Listeners\HandlePaymentSuccess;
use App\Listeners\HandlePaymentFailure;

protected $listen = [
    PaymentSucceeded::class => [HandlePaymentSuccess::class],
    PaymentFailed::class => [HandlePaymentFailure::class],
    PaymentCancelled::class => [HandlePaymentCancellation::class],
    PaymentTimedOut::class => [HandlePaymentTimeout::class],
    PaymentRefunded::class => [HandlePaymentRefund::class],
    PaymentAuthorized::class => [HandlePaymentAuthorization::class],
];
```

### Implement a Listener

```php
// app/Listeners/HandlePaymentSuccess.php

namespace App\Listeners;

use App\Models\Order;
use Netipar\SimplePay\Events\PaymentSucceeded;

class HandlePaymentSuccess
{
    public function handle(PaymentSucceeded $event): void
    {
        $ipn = $event->ipn;

        Order::where('order_ref', $ipn->orderRef)->update([
            'paid' => true,
            'transaction_id' => $ipn->transactionId,
            'paid_at' => $ipn->finishDate,
        ]);
    }
}
```

### Handle Payment Failure

```php
// app/Listeners/HandlePaymentFailure.php

namespace App\Listeners;

use App\Models\Order;
use Netipar\SimplePay\Events\PaymentFailed;

class HandlePaymentFailure
{
    public function handle(PaymentFailed $event): void
    {
        $ipn = $event->ipn;

        Order::where('order_ref', $ipn->orderRef)->update([
            'status' => 'failed',
        ]);
    }
}
```

## Available Events

| Event | Description |
|---|---|
| `IpnReceived` | Fired for every IPN notification received (regardless of status) |
| `PaymentSucceeded` | Payment completed successfully |
| `PaymentFailed` | Payment failed |
| `PaymentCancelled` | Payment was cancelled |
| `PaymentTimedOut` | Payment timed out |
| `PaymentRefunded` | Payment was refunded |
| `PaymentAuthorized` | Two-step payment was authorized (preauthorization) |

## IPN Data Properties

Every event carries an `$event->ipn` object with the following properties:

| Property | Type | Description |
|---|---|---|
| `$ipn->orderRef` | `string` | Your order reference |
| `$ipn->transactionId` | `int` | SimplePay transaction ID |
| `$ipn->status` | `string` | Payment status |
| `$ipn->currency` | `string` | Payment currency |
| `$ipn->total` | `float` | Payment total amount |
| `$ipn->finishDate` | `string` | Payment completion date |
| `$ipn->paymentMethod` | `string` | Payment method used |
| `$ipn->cardMask` | `?string` | Masked card number (if applicable) |

## Using the Generic IpnReceived Event

If you want to handle all IPN notifications in a single listener, use the `IpnReceived` event:

```php
use Netipar\SimplePay\Events\IpnReceived;

class HandleAllIpn
{
    public function handle(IpnReceived $event): void
    {
        $ipn = $event->ipn;

        logger()->info("IPN received for order {$ipn->orderRef}", [
            'status' => $ipn->status,
            'transactionId' => $ipn->transactionId,
            'total' => $ipn->total,
        ]);
    }
}
```

## Important Notes

- The IPN webhook is the **authoritative** source for payment status. Always use IPN events to update your order status, not the back URL redirect.
- The package automatically verifies the IPN signature before dispatching events.
- IPN requests are retried by SimplePay if your application does not respond with the expected confirmation. Make sure your listener logic is idempotent.
- The IPN route is excluded from CSRF verification automatically.
