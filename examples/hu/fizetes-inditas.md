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

// Redirect the customer to the payment page
return redirect($response->paymentUrl);
```

## Válasz objektum

A `start()` metódus által visszaadott válasz a következő adatokat tartalmazza:

```php
$response->transactionId; // SimplePay transaction ID (int)
$response->paymentUrl;    // Payment page URL to redirect the customer to
$response->timeout;       // Deadline for starting the payment (null for Request to Pay starts)
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

    // Billing address (required)
    invoice: new Address(
        name: 'Teszt Felhasználó',
        country: 'hu',
        city: 'Budapest',
        zip: '1111',
        address: 'Teszt utca 1.',
    ),

    // Delivery address (optional)
    delivery: new Address(
        name: 'Teszt Felhasználó',
        country: 'hu',
        city: 'Debrecen',
        zip: '4000',
        address: 'Szállítás utca 5.',
    ),

    // Customer name (optional)
    customer: 'Teszt Felhasználó',

    // Shipping cost (optional)
    shippingPrice: 1500,

    // Discount (optional)
    discount: 500,

    // Items
    items: [
        new Item(title: 'Laptop táska', price: 8500, quantity: 1),
        new Item(title: 'Egérpárna', price: 3000, quantity: 1),
    ],
));
```

## Eseményenkénti visszairányítási címek

Alapesetben a SimplePay minden kimenet után az `url` címre irányítja vissza a vásárlót. Ha kimenetenként külön oldalt szeretnél, add meg az `urls` mezőt. Ha az `urls` szerepel a kérésben, a SimplePay figyelmen kívül hagyja az `url`-t, ezért mind a négy eseményt meg kell adni.

```php
use Netipar\SimplePay\Dto\RedirectUrls;

$response = SimplePay::payment()->start(new PaymentRequest(
    currency: Currency::HUF,
    total: 2500,
    orderRef: 'ORDER-124',
    customerEmail: 'vasarlo@example.com',
    language: 'HU',
    url: route('simplepay.back'),
    urls: new RedirectUrls(
        success: route('payment.success'),
        fail: route('payment.failed'),
        cancel: route('checkout'),
        timeout: route('payment.timeout'),
    ),
));
```

Ezek az oldalak is megkapják az aláírt `r` és `s` paramétert; ellenőrizd őket a `SimplePay::payment()->handleBack($request->query('r'), $request->query('s'))` hívással.

## Rendelésazonosító újrahasználata

Az `orderRef` a rendelést azonosítja a rendszeredben. Egy sikertelen tranzakció rendelésazonosítója az első sikeres fizetésig újra elküldhető; minden új indítás új SimplePay `transactionId`-t kap, ezért a tranzakcióazonosítót próbálkozásonként tárold.

## Fizetési módszerek

A `methods` tömbben megadható értékek:

```php
use Netipar\SimplePay\Enums\PaymentMethod;

PaymentMethod::CARD    // Bank card
PaymentMethod::WIRE    // Bank transfer
```

## Pénznemek

```php
use Netipar\SimplePay\Enums\Currency;

Currency::HUF  // Hungarian forint
Currency::EUR  // Euro
Currency::USD  // US dollar
```
