# Google Pay a saját fizetési oldalon

A vásárlók eddig is fizethettek Google Pay-jel a SimplePay fizetőoldalán (`PaymentMethod::CARD`). Ez az oldal a SimplePay 2026-ban bevezetett, kereskedő oldali megoldását mutatja be: a Google Pay gomb a saját fizetési oldaladon jelenik meg, a backend pedig a `SimplePay::payment()->doGooglePay()` hívással terheli meg a Google Pay tokent.

A folyamat a SimplePay hivatalos Google Pay dokumentációját (2026.09.01.) és a SimplePay PHP SDK 2.1.5 `dogooglepay.php` mintáját követi.

## Előfeltételek

- A SimplePay kereskedői fiókodon engedélyezve van a Google Pay.
- Van Google Pay kereskedői azonosítód a Google Pay & Wallet Console-ból, és a Google jóváhagyta a weboldaladat (HTTPS domain, képernyőképek a fizetési folyamatról). Az azonosítót, a domaint és a képernyőképeket a SimplePay ügyfélszolgálatának kell elküldeni.
- A fizetési oldal megfelel a Google Pay [márkairányelveinek](https://developers.google.com/pay/api/web/guides/brand-guidelines) és [integrációs ellenőrzőlistájának](https://developers.google.com/pay/api/web/guides/test-and-deploy/integration-checklist).

## Konfiguráció

```env
SIMPLEPAY_GOOGLE_PAY_MERCHANT_ID=google_pay_kereskedoi_azonosito
SIMPLEPAY_GOOGLE_PAY_MERCHANT_NAME="Webáruházad neve"
```

A pénznemhez tartozó SimplePay kereskedői adatokat ugyanúgy használja, mint bármely más fizetésnél.

## A folyamat

1. A fizetési oldal a `googlePayConfig()` értékeivel jeleníti meg a Google Pay gombot.
2. Miután a vásárló jóváhagyta a fizetést a Google Pay felületén, az oldal elküldi a Base64-kódolt Google Pay tokent a backendnek.
3. A backend meghívja a `doGooglePay()`-t, és visszaadja az eredményt az oldalnak.
4. 3DS challenge nélkül a vásárló a fizetési oldalon marad, és a SimplePay nem irányítja vissza. Az eredményt az oldalon kell megjeleníteni.
5. Ha a kártyakibocsátó 3DS challenge-et kér, a válaszban `redirectUrl` érkezik, ide kell átirányítani a böngészőt. A challenge után a SimplePay a kérés `url` címére irányítja vissza a vásárlót az aláírt `r` és `s` paraméterekkel; ezeket a csomag `simplepay.back` route-ja kezeli.
6. A rendelést az IPN (vagy egy `query()`) alapján teljesítsd, soha ne csak a szinkron válasz vagy a visszairányítás alapján.

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

Az összeget, a rendelésazonosítót és a visszatérési `url`-t a saját adataidból vedd, ne a böngészőből.

### A kérés mezői

| Mező | Leírás |
|---|---|
| `currency`, `total`, `orderRef`, `customerEmail` | Mint egy normál fizetésnél |
| `googlePayToken` | A Google Pay `paymentMethodData.tokenizationData.token` értéke Base64-kódolva (a böngészőben `btoa()`) |
| `url` | Ide irányítja vissza a SimplePay a vásárlót a 3DS challenge után |
| `language` | Alapértéke `HU` |
| `type` | Alapértéke `TransactionType::Cit` |
| `threeDSReqAuthMethod` | A vásárló regisztrációja: `01` vendég, `02` regisztrált, `05` harmadik feles belépés |
| `invoice`, `customer` | Opcionális számlázási adatok |
| `timeout` | Másodpercben; alapértéke a `simplepay.timeout` |

### A válasz

| Tulajdonság | Leírás |
|---|---|
| `$response->transactionId` | SimplePay tranzakcióazonosító |
| `$response->orderRef` | A rendelésazonosítód |
| `$response->currency`, `$response->total` | Ha a SimplePay visszaadja |
| `$response->redirectUrl` | Csak akkor van értéke, ha 3DS challenge szükséges |
| `$response->requiresChallenge()` | `true`, ha van `redirectUrl` |

A 3DS challenge (`3004`-es hibakód) normál kimenet, nem dob kivételt. Minden más hibakód `SimplePayApiException`-t dob.

## Frontend

Add át a gomb beállításait a fizetési oldalnak:

```php
return view('checkout', [
    'order' => $order,
    'googlePay' => SimplePay::payment()->googlePayConfig(Currency::HUF, $order->total),
]);
```

| Kulcs | Érték |
|---|---|
| `gpayMerchantId`, `gpayMerchantName` | `simplepay.google_pay.merchant_id` és `merchant_name` |
| `gpayEnvironment` | Sandbox módban `TEST`, egyébként `PRODUCTION` |
| `gateway` | `simplepay` |
| `gatewayMerchantId` | A pénznemhez tartozó SimplePay kereskedői azonosító |
| `paymentType` | `CARD` |
| `total`, `currency` | A Google Pay felületen megjelenő összeg |

Egy minimális Blade-oldal a hivatalos minta alapján:

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

Inertia-oldalon (Vue vagy React) a `googlePayConfig()` eredményét propként add át, a `pay.js`-t a komponens betöltésekor töltsd be, és ugyanezeket a kérésobjektumokat használd.

## Tesztelés

Sandbox módban a gomb a Google `TEST` környezetében fut. A hivatalos ellenőrzőlista szerint ezeket kell tesztelni:

- sikeres fizetés 3DS nélkül, a fizetési oldalon maradva;
- sikertelen fizetés 3DS nélkül;
- a `3004`-es válasz és az átirányítás a `redirectUrl` címre;
- sikeres 3DS challenge és a visszatérés az `url` címre;
- sikertelen, megszakított és lejárt 3DS challenge;
- hiányzó vagy hibás `r`/`s` paraméter;
- a visszairányítás és az IPN tetszőleges sorrendben érkezik;
- a vásárló nem tér vissza az oldaladra.
