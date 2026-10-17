# Google Pay on Your Checkout Page

Customers can already pay with Google Pay on the SimplePay payment page (`PaymentMethod::CARD`). This page covers the merchant-side flow that SimplePay introduced in 2026: the Google Pay button sits on your own checkout page, and your backend charges the Google Pay token with `SimplePay::payment()->doGooglePay()`.

The flow follows the official SimplePay Google Pay documentation (2026-09-01) and the `dogooglepay.php` sample of the SimplePay PHP SDK 2.1.5.

## Prerequisites

- Google Pay is enabled for your SimplePay merchant account.
- You have a Google Pay merchant ID from the Google Pay & Wallet Console, and Google has approved your website (HTTPS domain, screenshots of the checkout). Send the merchant ID, the domain and the screenshots to SimplePay support.
- The checkout follows the Google Pay [brand guidelines](https://developers.google.com/pay/api/web/guides/brand-guidelines) and the [integration checklist](https://developers.google.com/pay/api/web/guides/test-and-deploy/integration-checklist).

## Configuration

```env
SIMPLEPAY_GOOGLE_PAY_MERCHANT_ID=your_google_pay_merchant_id
SIMPLEPAY_GOOGLE_PAY_MERCHANT_NAME="Your Shop"
```

The SimplePay merchant credentials of the currency are used as for any other payment.

## The Flow

1. The checkout page renders the Google Pay button with the values of `googlePayConfig()`.
2. After the customer approves the payment in the Google Pay sheet, the page posts the Base64 encoded Google Pay token to your backend.
3. Your backend calls `doGooglePay()` and returns the result to the page.
4. Without a 3DS challenge the customer stays on the checkout page, and SimplePay makes no back redirect. Show the result on the page.
5. If the card issuer asks for a 3DS challenge, the response has a `redirectUrl`: send the browser there. After the challenge SimplePay redirects the customer to the `url` of the request with the signed `r` and `s` parameters, which the package's `simplepay.back` route handles.
6. Fulfil the order from the IPN (or a `query()`), never from the synchronous response or the back redirect alone.

## Backend

```php
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Netipar\SimplePay\Dto\GooglePayDoRequest;
use Netipar\SimplePay\Enums\Currency;
use Netipar\SimplePay\Exceptions\SimplePayApiException;
use Netipar\SimplePay\Facades\SimplePay;

class GooglePayController
{
    public function __invoke(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'googlePayToken' => ['required', 'string'],
        ]);

        try {
            $response = SimplePay::payment()->doGooglePay(new GooglePayDoRequest(
                currency: Currency::HUF,
                total: $order->total,
                orderRef: $order->reference,
                customerEmail: $order->email,
                googlePayToken: $validated['googlePayToken'],
                url: route('simplepay.back'),
                threeDSReqAuthMethod: '02',
            ));
        } catch (SimplePayApiException $e) {
            return response()->json(['errorCodes' => $e->getErrorCodes()], 422);
        }

        return response()->json([
            'transactionId' => $response->transactionId,
            'redirectUrl' => $response->redirectUrl,
        ]);
    }
}
```

Take the amount, the order reference and the return `url` from your own data, not from the browser.

### Request Fields

| Field | Description |
|---|---|
| `currency`, `total`, `orderRef`, `customerEmail` | As for a normal payment |
| `googlePayToken` | `paymentMethodData.tokenizationData.token` from Google Pay, Base64 encoded (`btoa()` in the browser) |
| `url` | Where SimplePay sends the customer after a 3DS challenge |
| `language` | Defaults to `HU` |
| `type` | Defaults to `TransactionType::Cit` |
| `threeDSReqAuthMethod` | How the customer is registered: `01` guest, `02` registered, `05` third-party login |
| `invoice`, `customer` | Optional billing data |
| `timeout` | In seconds; defaults to `simplepay.timeout` |

### Response

| Property | Description |
|---|---|
| `$response->transactionId` | SimplePay transaction ID |
| `$response->orderRef` | Your order reference |
| `$response->currency`, `$response->total` | When SimplePay returns them |
| `$response->redirectUrl` | Set only when a 3DS challenge is required |
| `$response->requiresChallenge()` | `true` when `redirectUrl` is set |

A 3DS challenge (error code `3004`) is a normal outcome and does not throw. Every other error code throws `SimplePayApiException`.

## Frontend

Pass the button configuration to the checkout page:

```php
return view('checkout', [
    'order' => $order,
    'googlePay' => SimplePay::payment()->googlePayConfig(Currency::HUF, $order->total),
]);
```

| Key | Value |
|---|---|
| `gpayMerchantId`, `gpayMerchantName` | `simplepay.google_pay.merchant_id` and `merchant_name` |
| `gpayEnvironment` | `TEST` in sandbox mode, `PRODUCTION` otherwise |
| `gateway` | `simplepay` |
| `gatewayMerchantId` | Your SimplePay merchant ID for the currency |
| `paymentType` | `CARD` |
| `total`, `currency` | The amount shown in the Google Pay sheet |

A minimal Blade page, based on the official sample:

```blade
<div id="gpay-container"></div>

<script>
    const simplePayTransaction = @json($googlePay);
    const authorizationEndpoint = @json(route('checkout.google-pay', $order));

    const baseRequest = {
        apiVersion: 2,
        apiVersionMinor: 0,
        allowedPaymentMethods: [{
            type: simplePayTransaction.paymentType,
            parameters: {
                allowedAuthMethods: ['PAN_ONLY', 'CRYPTOGRAM_3DS'],
                // Add 'AMEX' if your SimplePay contract accepts American Express.
                allowedCardNetworks: ['MASTERCARD', 'VISA'],
            },
            tokenizationSpecification: {
                type: 'PAYMENT_GATEWAY',
                parameters: {
                    gateway: simplePayTransaction.gateway,
                    gatewayMerchantId: simplePayTransaction.gatewayMerchantId,
                },
            },
        }],
        merchantInfo: {
            merchantId: simplePayTransaction.gpayMerchantId,
            merchantName: simplePayTransaction.gpayMerchantName,
        },
    };

    let paymentsClient = null;

    function client() {
        paymentsClient ??= new google.payments.api.PaymentsClient({
            environment: simplePayTransaction.gpayEnvironment,
        });

        return paymentsClient;
    }

    async function onGooglePayLoaded() {
        const { result } = await client().isReadyToPay(baseRequest);

        if (result) {
            document.getElementById('gpay-container')
                .appendChild(client().createButton({ onClick: pay }));
        }
    }

    async function pay() {
        const paymentData = await client().loadPaymentData({
            ...baseRequest,
            transactionInfo: {
                countryCode: 'HU',
                currencyCode: simplePayTransaction.currency,
                totalPriceStatus: 'FINAL',
                totalPrice: simplePayTransaction.total,
            },
        });

        const response = await fetch(authorizationEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': @json(csrf_token()),
            },
            body: JSON.stringify({
                googlePayToken: btoa(paymentData.paymentMethodData.tokenizationData.token),
            }),
        });

        const result = await response.json();

        if (result.redirectUrl) {
            // 3DS challenge: SimplePay returns the customer to the request's url afterwards.
            window.location.replace(result.redirectUrl);

            return;
        }

        // No 3DS challenge: show the result here; the order is fulfilled from the IPN.
    }
</script>
<script async src="https://pay.google.com/gp/p/js/pay.js" onload="onGooglePayLoaded()"></script>
```

In an Inertia (Vue or React) page pass `googlePayConfig()` as a prop, load `pay.js` when the component mounts, and use the same request objects.

## Testing

In sandbox mode the button runs in Google's `TEST` environment. The official checklist asks you to test:

- a successful payment without 3DS, staying on the checkout page;
- a failed payment without 3DS;
- the `3004` response and the redirect to `redirectUrl`;
- a successful 3DS challenge and the return to `url`;
- a failed, cancelled and expired 3DS challenge;
- a missing or invalid `r`/`s` parameter;
- the back redirect and the IPN arriving in either order;
- a customer who never returns to your site.
