# Back URL Handling

The package automatically registers a `GET /simplepay/back` route. When a customer completes (or abandons) a payment on the SimplePay payment page, they are redirected to this URL, and the package processes the response.

## Automatic Route Registration

The package automatically registers the back URL route:

```
GET /simplepay/back
```

No manual route setup is needed. The package handles signature verification and creates the `BackResponse` object.

## Customizing the Response (Fortify-style)

You can customize the redirect logic by binding your own `BackUrlResponse` implementation in your `AppServiceProvider`:

### 1. Create Your Response Class

```php
// app/Http/Responses/SimplePayBackResponse.php
namespace App\Http\Responses;

use Illuminate\Http\Request;
use Netipar\SimplePay\Contracts\BackUrlResponse;
use Netipar\SimplePay\Dto\BackResponse;
use Netipar\SimplePay\Enums\BackEvent;
use Symfony\Component\HttpFoundation\Response;

class SimplePayBackResponse implements BackUrlResponse
{
    public function toResponse(Request $request, BackResponse $back): Response
    {
        return match ($back->event) {
            BackEvent::Success => redirect()->route('payment.success', ['order' => $back->orderRef]),
            BackEvent::Fail    => redirect()->route('payment.failed'),
            BackEvent::Cancel  => redirect()->route('payment.cancelled'),
            BackEvent::Timeout => redirect()->route('payment.timeout'),
        };
    }
}
```

### 2. Register in AppServiceProvider

```php
// app/Providers/AppServiceProvider.php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Netipar\SimplePay\Contracts\BackUrlResponse;
use App\Http\Responses\SimplePayBackResponse;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(BackUrlResponse::class, SimplePayBackResponse::class);
    }
}
```

## Response Properties

The `BackResponse` object contains the following properties:

| Property | Type | Description |
|---|---|---|
| `$back->event` | `BackEvent` | The payment outcome event |
| `$back->transactionId` | `int` | SimplePay transaction ID |
| `$back->orderRef` | `string` | Your order reference |
| `$back->merchant` | `string` | Merchant ID |
| `$back->total` | `float` | Payment total amount |

## Back Events

| Event | Description |
|---|---|
| `BackEvent::Success` | Payment completed successfully |
| `BackEvent::Fail` | Payment failed |
| `BackEvent::Cancel` | Customer cancelled the payment |
| `BackEvent::Timeout` | Payment timed out |

## Example: Advanced Handling

```php
namespace App\Http\Responses;

use Illuminate\Http\Request;
use Netipar\SimplePay\Contracts\BackUrlResponse;
use Netipar\SimplePay\Dto\BackResponse;
use Netipar\SimplePay\Enums\BackEvent;
use Symfony\Component\HttpFoundation\Response;

class SimplePayBackResponse implements BackUrlResponse
{
    public function toResponse(Request $request, BackResponse $back): Response
    {
        return match ($back->event) {
            BackEvent::Success => $this->handleSuccess($back),
            BackEvent::Fail    => $this->handleFailure($back),
            BackEvent::Cancel  => $this->handleCancel($back),
            BackEvent::Timeout => $this->handleTimeout($back),
        };
    }

    private function handleSuccess(BackResponse $back): Response
    {
        return response()->view('payment.success', [
            'orderRef' => $back->orderRef,
            'transactionId' => $back->transactionId,
            'total' => $back->total,
        ]);
    }

    private function handleFailure(BackResponse $back): Response
    {
        return response()->view('payment.failed', [
            'orderRef' => $back->orderRef,
        ]);
    }

    private function handleCancel(BackResponse $back): Response
    {
        return redirect()
            ->route('checkout')
            ->with('warning', 'Payment was cancelled.');
    }

    private function handleTimeout(BackResponse $back): Response
    {
        return response()->view('payment.timeout', [
            'orderRef' => $back->orderRef,
        ]);
    }
}
```

## Important Notes

- The back URL does **not** confirm payment success. Always rely on the [IPN webhook](ipn-webhook.md) for authoritative payment status updates.
- The `r` and `s` query parameters are provided by SimplePay for response verification.
- The signature (`s`) is automatically validated by the package to ensure the response has not been tampered with.
- If you don't register a custom `BackUrlResponse` implementation, the default response redirects to `/`.
