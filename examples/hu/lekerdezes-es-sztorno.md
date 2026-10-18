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

$query->status;         // Transaction status (PaymentStatus)
$query->total;          // Transaction total (float)
$query->orderRef;       // Order reference (string)
$query->remainingTotal; // Remaining amount after refunds (?float)
$query->paymentDate;    // Payment date (?string)
$query->finishDate;     // Finish date (?string)
$query->method;         // Payment method, e.g. "CARD" (?string)
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
    amount: 2500, // The full amount
));

$refund->remainingTotal; // 0 (everything refunded)
```

### Részleges visszatérítés

```php
$refund = SimplePay::payment()->refund(new RefundRequest(
    currency: Currency::HUF,
    transactionId: 123456,
    orderRef: 'ORDER-123',
    amount: 1000, // Refund only HUF 1,000
));

$refund->remainingTotal; // 1500 (the remaining amount)
```

### Többszörös részleges visszatérítés

Lehetséges többször is részlegesen visszatéríteni, amíg a teljes összeg el nem fogy:

```php
// First partial refund
$refund1 = SimplePay::payment()->refund(new RefundRequest(
    currency: Currency::HUF,
    transactionId: 123456,
    orderRef: 'ORDER-123',
    amount: 500,
));
// $refund1->remainingTotal => 2000

// Second partial refund
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
use Netipar\SimplePay\Dto\FinishRequest;

$finish = SimplePay::payment()->finish(new FinishRequest(
    currency: Currency::HUF,
    transactionId: 123456,
    orderRef: 'ORDER-PREAUTH-001',
    amount: 15000,        // the amount to capture
    originalTotal: 15000, // the authorized amount
));
```

A `finish()` meghívásakor az összeg lehet kisebb is, mint az eredetileg zárolt összeg (részleges terhelés).

```php
// HUF 15,000 was authorized
// Capture only HUF 12,000
$finish = SimplePay::payment()->finish(new FinishRequest(
    currency: Currency::HUF,
    transactionId: 123456,
    orderRef: 'ORDER-PREAUTH-001',
    amount: 12000,
    originalTotal: 15000,
));
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
