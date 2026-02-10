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
```

## Konfigurációs struktúra

```php
// config/simplepay.php
return [

    /*
    |--------------------------------------------------------------------------
    | Sandbox mód
    |--------------------------------------------------------------------------
    |
    | Sandbox módban a SimplePay teszt környezetet használja.
    | Éles üzemben állítsd false-ra.
    |
    */
    'sandbox' => env('SIMPLEPAY_SANDBOX', true),

    /*
    |--------------------------------------------------------------------------
    | Kereskedői fiókok pénznemenként
    |--------------------------------------------------------------------------
    |
    | Minden pénznemhez külön merchant ID és secret key tartozik.
    | Csak azokat a pénznemeket kell megadni, amelyeket használsz.
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
    | API URL-ek
    |--------------------------------------------------------------------------
    |
    | A SimplePay sandbox és éles API URL-jei.
    | Általában nem kell módosítani.
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
    | Az alapértelmezett visszatérítési URL, ahova a vásárló érkezik
    | a fizetés befejezése után. Felülírhatod a PaymentRequest-ben.
    |
    */
    'back_url' => env('SIMPLEPAY_BACK_URL'),

    /*
    |--------------------------------------------------------------------------
    | Log csatorna
    |--------------------------------------------------------------------------
    |
    | A SimplePay kommunikáció logolásához használt csatorna.
    | Ha null, a default log csatornát használja.
    |
    */
    'log_channel' => env('SIMPLEPAY_LOG_CHANNEL'),

    /*
    |--------------------------------------------------------------------------
    | Időtúllépés (timeout)
    |--------------------------------------------------------------------------
    |
    | A fizetési ablak időtúllépése másodpercben.
    | Az alapérték 600 másodperc (10 perc).
    |
    */
    'timeout' => env('SIMPLEPAY_TIMEOUT', 600),

    /*
    |--------------------------------------------------------------------------
    | Auto challenge (3DS)
    |--------------------------------------------------------------------------
    |
    | Automatikus 3D Secure challenge kérés.
    |
    */
    'auto_challenge' => env('SIMPLEPAY_AUTO_CHALLENGE', true),

];
```

## Pénznemenként kereskedői fiókok

A SimplePay rendszerben minden pénznemhez külön kereskedői fiók tartozik. Csak azokat kell beállítanod, amelyeket használni kívánod:

```env
# Ha csak HUF-ban fogadsz fizetéseket:
SIMPLEPAY_HUF_MERCHANT=your_huf_merchant_id
SIMPLEPAY_HUF_SECRET_KEY=your_huf_secret_key

# Ha EUR-ban is fogadsz:
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
    // ...meglévő csatornák...

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
SIMPLEPAY_TIMEOUT=900  # 15 perc
```

Ez azt jelenti, hogy a vásárlónak ennyi ideje van befejezni a fizetést a SimplePay oldalán.

## Auto challenge (3DS)

Az `auto_challenge` beállítás szabályozza, hogy a package automatikusan kéri-e a 3D Secure hitelesítési kivételt. Alapértelmezetten be van kapcsolva:

```env
SIMPLEPAY_AUTO_CHALLENGE=true
```
