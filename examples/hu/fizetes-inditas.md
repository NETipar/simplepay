# Fizetés indítása

A `SimplePay::payment()->start()` metódus elindít egy fizetési tranzakciót a SimplePay rendszerén keresztül, és visszaad egy URL-t, ahova a vásárlót át kell irányítani.

## Alap fizetés

```php
use Netipar\SimplePay\Facades\SimplePay;
use Netipar\SimplePay\Enums\Currency;
use Netipar\SimplePay\Enums\PaymentMethod;
use Netipar\SimplePay\Dto\PaymentRequest;
use Netipar\SimplePay\Dto\Address;
use Netipar\SimplePay\Dto\Item;

$response = SimplePay::payment()->start(new PaymentRequest(
    currency: Currency::HUF,
    total: 2500,
    orderRef: 'ORDER-123',
    customerEmail: 'vasarlo@example.com',
    language: 'HU',
    url: route('simplepay.back'),
    methods: [PaymentMethod::CARD],
    invoice: new Address(
        name: 'Teszt Felhasználó',
        country: 'hu',
        city: 'Budapest',
        zip: '1111',
        address: 'Teszt utca 1.',
    ),
    items: [
        new Item(title: 'Termék 1', price: 1500, quantity: 1),
        new Item(title: 'Termék 2', price: 1000, quantity: 1),
    ],
));

// Átirányítás a fizetési oldalra
return redirect($response->paymentUrl);
```

## Válasz objektum

A `start()` metódus által visszaadott válasz a következő adatokat tartalmazza:

```php
$response->transactionId; // SimplePay tranzakció azonosító (int)
$response->paymentUrl;    // Fizetési oldal URL, ahova a vásárlót át kell irányítani
$response->timeout;       // Időtúllépés másodpercben
```

## Kétlépcsős fizetés (preautorizáció)

Kétlépcsős fizetés esetén az összeg csak zárolásra kerül a kártyáról, a tényleges terhelés később történik a `finish()` metódus hívásával.

```php
$response = SimplePay::payment()->start(new PaymentRequest(
    currency: Currency::HUF,
    total: 15000,
    orderRef: 'ORDER-PREAUTH-001',
    customerEmail: 'vasarlo@example.com',
    language: 'HU',
    url: route('simplepay.back'),
    methods: [PaymentMethod::CARD],
    twoStep: true,
    invoice: new Address(
        name: 'Teszt Felhasználó',
        country: 'hu',
        city: 'Budapest',
        zip: '1111',
        address: 'Teszt utca 1.',
    ),
    items: [
        new Item(title: 'Előjegyzés', price: 15000, quantity: 1),
    ],
));

return redirect($response->paymentUrl);
```

A zárolást később a `finish()` metódus hívja meg (lásd: [Lekérdezés és sztornó](lekerdezes-es-sztorno.md)).

## Kártyatárolással együtt

Ha szeretnéd a vásárló kártyáját tárolni a következő fizetésekhez, használd a `recurring` tömböt:

```php
$response = SimplePay::payment()->start(new PaymentRequest(
    currency: Currency::HUF,
    total: 5000,
    orderRef: 'ORDER-REC-001',
    customerEmail: 'vasarlo@example.com',
    language: 'HU',
    url: route('simplepay.back'),
    methods: [PaymentMethod::CARD],
    recurring: [
        'times' => 3,
        'until' => '2025-12-31T00:00:00+02:00',
        'maxAmount' => 50000,
    ],
    invoice: new Address(
        name: 'Teszt Felhasználó',
        country: 'hu',
        city: 'Budapest',
        zip: '1111',
        address: 'Teszt utca 1.',
    ),
    items: [
        new Item(title: 'Előfizetés', price: 5000, quantity: 1),
    ],
));

return redirect($response->paymentUrl);
```

A `recurring` tömb paraméterei:

| Paraméter    | Leírás                                               |
|-------------|-------------------------------------------------------|
| `times`     | Generálandó tokenek száma (hány terhelés, legalább `1`) |
| `until`     | Meddig érvényes a tárolási engedély                   |
| `maxAmount` | Maximális összeg egy terhelésnél                      |

## Opcionális mezők

```php
$response = SimplePay::payment()->start(new PaymentRequest(
    currency: Currency::HUF,
    total: 12500,
    orderRef: 'ORDER-FULL-001',
    customerEmail: 'vasarlo@example.com',
    language: 'HU',
    url: route('simplepay.back'),
    methods: [PaymentMethod::CARD],

    // Számlázási cím (kötelező)
    invoice: new Address(
        name: 'Teszt Felhasználó',
        country: 'hu',
        city: 'Budapest',
        zip: '1111',
        address: 'Teszt utca 1.',
    ),

    // Szállítási cím (opcionális)
    delivery: new Address(
        name: 'Teszt Felhasználó',
        country: 'hu',
        city: 'Debrecen',
        zip: '4000',
        address: 'Szállítás utca 5.',
    ),

    // Vásárló adatai (opcionális)
    customer: 'Teszt Felhasználó',

    // Szállítási költség (opcionális)
    shippingPrice: 1500,

    // Kedvezmény (opcionális)
    discount: 500,

    // Tételek
    items: [
        new Item(title: 'Laptop táska', price: 8500, quantity: 1),
        new Item(title: 'Egérpárna', price: 3000, quantity: 1),
    ],
));
```

## Fizetési módszerek

A `methods` tömbben megadható értékek:

```php
use Netipar\SimplePay\Enums\PaymentMethod;

PaymentMethod::CARD    // Bankkártyával
PaymentMethod::WIRE    // Átutalással
```

## Pénznemek

```php
use Netipar\SimplePay\Enums\Currency;

Currency::HUF  // Magyar forint
Currency::EUR  // Euró
Currency::USD  // Amerikai dollár
```
