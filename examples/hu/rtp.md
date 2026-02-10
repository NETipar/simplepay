# Request to Pay (RTP)

A `SimplePay::rtp()` metódusain keresztül kezelheted a Request to Pay (azonnali fizetési kérés) funkciókat. Az RTP lehetővé teszi, hogy fizetési kérést küldj a vásárlónak, aki azt az online bankján keresztül fogadja el.

## RTP indítás

Egyedi fizetési kérés indítása:

```php
use Netipar\SimplePay\Facades\SimplePay;
use Netipar\SimplePay\Enums\Currency;
use Netipar\SimplePay\Dto\RtpStartRequest;

$response = SimplePay::rtp()->start(new RtpStartRequest(
    currency: Currency::HUF,
    total: 5000,
    orderRef: 'RTP-ORDER-001',
    customerEmail: 'vasarlo@example.com',
    url: route('simplepay.back'),
    customer: 'Teszt Vásárló',
));

// Válasz adatok
$response->paymentUrl;    // URL a fizetési oldalhoz
$response->transactionId; // SimplePay tranzakció azonosító
```

## Kötegelt RTP

Több fizetési kérés egyidejűleg történő küldése:

```php
use Netipar\SimplePay\Dto\RtpDoRequest;
use Netipar\SimplePay\Dto\RtpPayment;

SimplePay::rtp()->do(new RtpDoRequest(
    currency: Currency::HUF,
    payments: [
        new RtpPayment(
            orderRef: 'RTP-001',
            total: 1000,
            customerEmail: 'a@example.com',
            customer: 'Vásárló A',
        ),
        new RtpPayment(
            orderRef: 'RTP-002',
            total: 2000,
            customerEmail: 'b@example.com',
            customer: 'Vásárló B',
        ),
        new RtpPayment(
            orderRef: 'RTP-003',
            total: 3500,
            customerEmail: 'c@example.com',
            customer: 'Vásárló C',
        ),
    ],
));
```

## RTP lekérdezés

Egy RTP tranzakció állapotának lekérdezése:

```php
$query = SimplePay::rtp()->query(Currency::HUF, transactionId: '12345');
```

## RTP visszatérítés (refund)

RTP tranzakció visszatérítése:

```php
SimplePay::rtp()->refund(Currency::HUF, '12345', 1000);
```

A harmadik paraméter a visszatérítendő összeg. Részleges visszatérítés is lehetséges.

## RTP sztornó (reverse)

RTP tranzakció teljes visszavonása:

```php
SimplePay::rtp()->reverse(Currency::HUF, '12345');
```

## Összefoglaló

| Művelet          | Metódus     | Leírás                                             |
|------------------|-------------|-----------------------------------------------------|
| RTP indítás      | `start()`   | Egyedi fizetési kérés küldése                       |
| Kötegelt RTP     | `do()`      | Több fizetési kérés egyidejűleg                     |
| Lekérdezés       | `query()`   | Tranzakció állapotának lekérdezése                  |
| Visszatérítés    | `refund()`  | Részleges vagy teljes visszatérítés                 |
| Sztornó          | `reverse()` | Tranzakció teljes visszavonása                      |
