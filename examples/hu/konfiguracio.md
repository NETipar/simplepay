# Konfiguráció

A `netipar/simplepay` package konfigurációjának beállítása.

## Konfigurációs fájl publikálása

```bash
php artisan vendor:publish --tag=simplepay-config
```

Ez létrehozza a `config/simplepay.php` fájlt.

## Környezeti változók (.env)

```env
SIMPLEPAY_SANDBOX=true
SIMPLEPAY_HUF_MERCHANT=your_huf_merchant_id
SIMPLEPAY_HUF_SECRET_KEY=your_huf_secret_key
SIMPLEPAY_EUR_MERCHANT=your_eur_merchant_id
SIMPLEPAY_EUR_SECRET_KEY=your_eur_secret_key
SIMPLEPAY_BACK_URL=https://yourdomain.com/payment/back
SIMPLEPAY_LOG_CHANNEL=simplepay
SIMPLEPAY_TIMEOUT=600
SIMPLEPAY_GOOGLE_PAY_MERCHANT_ID=your_google_pay_merchant_id
SIMPLEPAY_GOOGLE_PAY_MERCHANT_NAME="Your Shop"
```

## Konfigurációs struktúra

```php
// config/simplepay.php
return [

    /*
    |--------------------------------------------------------------------------
    | Sandbox Mode
    |--------------------------------------------------------------------------
    |
    | In sandbox mode the SimplePay test environment is used.
    | Set it to false in production.
    |
    */
    'sandbox' => env('SIMPLEPAY_SANDBOX', true),

    /*
    |--------------------------------------------------------------------------
    | Merchant Accounts per Currency
    |--------------------------------------------------------------------------
    |
    | Each currency has its own merchant ID and secret key.
    | Only configure the currencies you use.
    |
    */
    'merchants' => [
        'HUF' => [
            'merchant' => env('SIMPLEPAY_HUF_MERCHANT'),
            'secret_key' => env('SIMPLEPAY_HUF_SECRET_KEY'),
        ],
        'EUR' => [
            'merchant' => env('SIMPLEPAY_EUR_MERCHANT'),
            'secret_key' => env('SIMPLEPAY_EUR_SECRET_KEY'),
        ],
        'USD' => [
            'merchant' => env('SIMPLEPAY_USD_MERCHANT'),
            'secret_key' => env('SIMPLEPAY_USD_SECRET_KEY'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | API URLs
    |--------------------------------------------------------------------------
    |
    | The sandbox and live API URLs of SimplePay.
    | Usually there is no need to change them.
    |
    */
    'urls' => [
        'sandbox' => 'https://sandbox.simplepay.hu/payment/v2',
        'live' => 'https://secure.simplepay.hu/payment/v2',
    ],

    /*
    |--------------------------------------------------------------------------
    | Back URL
    |--------------------------------------------------------------------------
    |
    | The default back URL where the customer arrives after the
    | payment. You can override it in the PaymentRequest.
    |
    */
    'back_url' => env('SIMPLEPAY_BACK_URL'),

    /*
    |--------------------------------------------------------------------------
    | Log Channel
    |--------------------------------------------------------------------------
    |
    | The log channel of the SimplePay communication.
    | Set it to null to disable logging.
    |
    */
    'log_channel' => env('SIMPLEPAY_LOG_CHANNEL'),

    /*
    |--------------------------------------------------------------------------
    | Timeout
    |--------------------------------------------------------------------------
    |
    | The payment window timeout in seconds.
    | The default is 600 seconds (10 minutes).
    |
    */
    'timeout' => env('SIMPLEPAY_TIMEOUT', 600),

    /*
    |--------------------------------------------------------------------------
    | Google Pay
    |--------------------------------------------------------------------------
    |
    | The Google Pay merchant ID and name for the Google Pay button on your
    | own checkout page.
    |
    */
    'google_pay' => [
        'merchant_id' => env('SIMPLEPAY_GOOGLE_PAY_MERCHANT_ID'),
        'merchant_name' => env('SIMPLEPAY_GOOGLE_PAY_MERCHANT_NAME'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto Challenge (3DS)
    |--------------------------------------------------------------------------
    |
    | Automatic 3D Secure challenge request.
    |
    */
    'auto_challenge' => env('SIMPLEPAY_AUTO_CHALLENGE', true),

];
```

## Pénznemenként kereskedői fiókok

A SimplePay rendszerben minden pénznemhez külön kereskedői fiók tartozik. Csak azokat kell beállítanod, amelyeket használni kívánod:

```env
# If you only accept HUF payments:
SIMPLEPAY_HUF_MERCHANT=your_huf_merchant_id
SIMPLEPAY_HUF_SECRET_KEY=your_huf_secret_key

# If you also accept EUR:
SIMPLEPAY_EUR_MERCHANT=your_eur_merchant_id
SIMPLEPAY_EUR_SECRET_KEY=your_eur_secret_key
```

## Sandbox mód

Fejlesztés és tesztelés során a sandbox módot használd:

```env
SIMPLEPAY_SANDBOX=true
```

Az éles rendszerre való átálláskor egyszerűen állítsd `false`-ra:

```env
SIMPLEPAY_SANDBOX=false
```

Sandbox módban a SimplePay teszt API-ját használja a package (`sandbox.simplepay.hu`), éles módban a `secure.simplepay.hu` címre küldi a kéréseket.

## Dedikált log csatorna beállítása

Ha külön log fájlba szeretnéd gyűjteni a SimplePay kommunikációt, hozz létre egy dedikált csatornát:

```php
// config/logging.php
'channels' => [
    // ...existing channels...

    'simplepay' => [
        'driver' => 'daily',
        'path' => storage_path('logs/simplepay.log'),
        'level' => 'debug',
        'days' => 30,
    ],
],
```

Ezután állítsd be a `.env` fájlban:

```env
SIMPLEPAY_LOG_CHANNEL=simplepay
```

Így a SimplePay API kommunikáció külön fájlba kerül (`storage/logs/simplepay.log`), és nem keveredik az alkalmazás többi logjaival.

## Időtúllépés

Az alapértelmezett időtúllépés 600 másodperc (10 perc). Ha módosítani szeretnéd:

```env
SIMPLEPAY_TIMEOUT=900  # 15 minutes
```

Ez azt jelenti, hogy a vásárlónak ennyi ideje van befejezni a fizetést a SimplePay oldalán.

## Auto challenge (3DS)

Az `auto_challenge` beállítás szabályozza, hogy a package automatikusan kéri-e a 3D Secure hitelesítési kivételt. Alapértelmezetten be van kapcsolva:

```env
SIMPLEPAY_AUTO_CHALLENGE=true
```

## Google Pay

A `google_pay.merchant_id` és a `google_pay.merchant_name` értéke a Google Pay & Wallet Console-ból származik. Csak akkor kell, ha a Google Pay gomb a saját fizetési oldaladon jelenik meg; lásd [Google Pay a saját fizetési oldalon](google-pay.md).
