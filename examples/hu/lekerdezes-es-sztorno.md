# Lekérdezés, sztornó, visszatérítés

Tranzakciók állapotának lekérdezése, sztornózása és visszatérítése a `SimplePay::payment()` metódusain keresztül.

## Tranzakció lekérdezése

```php
use Netipar\SimplePay\Facades\SimplePay;
use Netipar\SimplePay\Enums\Currency;

$query = SimplePay::payment()->query(
    currency: Currency::HUF,
    transactionId: 123456,
);

$query->status;         // Tranzakció státusza (PaymentStatus)
$query->total;          // Tranzakció összege (float)
$query->orderRef;       // Megrendelési hivatkozás (string)
$query->remainingTotal; // Hátralévő összeg visszatérítés után (?float)
$query->paymentDate;    // Fizetés dátuma (?string)
$query->finishDate;     // Lezárás dátuma (?string)
$query->method;         // Fizetési mód, pl. "CARD" (?string)
```

## Visszatérítés (refund)

Visszatérítés esetén az összeg (részben vagy egészben) visszakerül a vásárló számlájára.

### Teljes visszatérítés

```php
use Netipar\SimplePay\Dto\RefundRequest;

$refund = SimplePay::payment()->refund(new RefundRequest(
    currency: Currency::HUF,
    transactionId: 123456,
    orderRef: 'ORDER-123',
    amount: 2500, // A teljes összeg
));

$refund->remainingTotal; // 0 (minden visszatérítve)
```

### Részleges visszatérítés

```php
$refund = SimplePay::payment()->refund(new RefundRequest(
    currency: Currency::HUF,
    transactionId: 123456,
    orderRef: 'ORDER-123',
    amount: 1000, // Csak 1000 Ft visszatérítése
));

$refund->remainingTotal; // 1500 (a fennmaradó összeg)
```

### Többszörös részleges visszatérítés

Lehetséges többször is részlegesen visszatéríteni, amíg a teljes összeg el nem fogy:

```php
// Első részleges visszatérítés
$refund1 = SimplePay::payment()->refund(new RefundRequest(
    currency: Currency::HUF,
    transactionId: 123456,
    orderRef: 'ORDER-123',
    amount: 500,
));
// $refund1->remainingTotal => 2000

// Második részleges visszatérítés
$refund2 = SimplePay::payment()->refund(new RefundRequest(
    currency: Currency::HUF,
    transactionId: 123456,
    orderRef: 'ORDER-123',
    amount: 2000,
));
// $refund2->remainingTotal => 0
```

## Sztornó (cancel)

A sztornó teljesen visszavonja a tranzakciót. Csak még nem feldolgozott tranzakcióknál használható.

```php
$cancel = SimplePay::payment()->cancel(
    currency: Currency::HUF,
    transactionId: 123456,
);
```

## Kétlépcsős fizetés befejezése (finish)

Ha a fizetést kétlépcsős módban indítottad (`twoStep: true`), a zárolt összeget a `finish()` metódussal terhelheted:

```php
$finish = SimplePay::payment()->finish(
    currency: Currency::HUF,
    transactionId: 123456,
    orderRef: 'ORDER-PREAUTH-001',
    amount: 15000,
);
```

A `finish()` meghívásakor az összeg lehet kisebb is, mint az eredetileg zárolt összeg (részleges terhelés).

```php
// Eredetileg 15000 Ft zárolt
// Csak 12000 Ft terhelése
$finish = SimplePay::payment()->finish(
    currency: Currency::HUF,
    transactionId: 123456,
    orderRef: 'ORDER-PREAUTH-001',
    amount: 12000,
);
```

## Hibakezelés

Az API hibaválaszok `SimplePayApiException` kivételt dobnak, ami tartalmazza a hibakódot és annak leírását:

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
}
```

> **Tipp:** Az `AUTHORIZED` státuszú (kétlépcsős) tranzakcióknál a `refund()` nem használható. Használd a `cancel()` metódust a zárolás feloldásához, vagy a `finish()` metódust a terheléshez.

## Összefoglaló

| Művelet                | Metódus     | Mikor használd                                      |
|------------------------|-------------|-----------------------------------------------------|
| Lekérdezés             | `query()`   | Tranzakció aktuális állapotának lekérdezése          |
| Visszatérítés          | `refund()`  | Részleges vagy teljes visszatérítés a vásárlónak     |
| Sztornó                | `cancel()`  | Tranzakció teljes visszavonása                       |
| Kétlépcsős befejezése  | `finish()`  | Preautorizált összeg tényleges terhelése             |
