# IPN webhook kezelése

Az IPN (Instant Payment Notification) a SimplePay szerver-szerver kommunikációja, amely automatikusan értesíti az alkalmazást a tranzakciók állapotváltozásairól. A package automatikusan regisztrálja a szükséges route-ot.

## Automatikus route regisztráció

A package automatikusan regisztrálja az IPN webhook-ot a következő URL-en:

```
POST /simplepay/ipn
```

Nem szükséges külön route-ot felvenni. A package kezeli az aláírások ellenőrzését és a válaszok visszaküldését.

## Event Listener-ek beállítása

Az IPN feldolgozás után a package Laravel event-eket dispatchel. Ezeket a szokásos módon hallgathatod:

### EventServiceProvider-ben

```php
// app/Providers/EventServiceProvider.php
namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Netipar\SimplePay\Events\PaymentSucceeded;
use Netipar\SimplePay\Events\PaymentFailed;
use Netipar\SimplePay\Events\PaymentCancelled;
use Netipar\SimplePay\Events\PaymentTimedOut;
use Netipar\SimplePay\Events\PaymentRefunded;
use Netipar\SimplePay\Events\PaymentAuthorized;
use Netipar\SimplePay\Events\IpnReceived;
use App\Listeners\HandlePaymentSuccess;
use App\Listeners\HandlePaymentFailure;
use App\Listeners\HandlePaymentCancellation;
use App\Listeners\HandlePaymentTimeout;
use App\Listeners\HandlePaymentRefund;
use App\Listeners\HandlePaymentAuthorization;
use App\Listeners\LogIpnNotification;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        PaymentSucceeded::class  => [HandlePaymentSuccess::class],
        PaymentFailed::class     => [HandlePaymentFailure::class],
        PaymentCancelled::class  => [HandlePaymentCancellation::class],
        PaymentTimedOut::class   => [HandlePaymentTimeout::class],
        PaymentRefunded::class   => [HandlePaymentRefund::class],
        PaymentAuthorized::class => [HandlePaymentAuthorization::class],
        IpnReceived::class       => [LogIpnNotification::class],
    ];
}
```

## Listener példa: sikeres fizetés

```php
// app/Listeners/HandlePaymentSuccess.php
namespace App\Listeners;

use App\Models\Order;
use Netipar\SimplePay\Events\PaymentSucceeded;

class HandlePaymentSuccess
{
    public function handle(PaymentSucceeded $event): void
    {
        $ipn = $event->ipn;

        Order::where('order_ref', $ipn->orderRef)->update([
            'paid' => true,
            'transaction_id' => $ipn->transactionId,
            'paid_at' => $ipn->finishDate,
        ]);
    }
}
```

## Listener példa: sikertelen fizetés

```php
// app/Listeners/HandlePaymentFailure.php
namespace App\Listeners;

use App\Models\Order;
use App\Notifications\PaymentFailedNotification;
use Netipar\SimplePay\Events\PaymentFailed;

class HandlePaymentFailure
{
    public function handle(PaymentFailed $event): void
    {
        $ipn = $event->ipn;

        $order = Order::where('order_ref', $ipn->orderRef)->first();

        if ($order) {
            $order->update(['status' => 'failed']);
            $order->user->notify(new PaymentFailedNotification($order));
        }
    }
}
```

## Összes esemény

| Esemény                | Mikor kerül kiváltásra                                              |
|------------------------|---------------------------------------------------------------------|
| `IpnReceived`          | Minden IPN értesítésnél, függetlenül a státusztól                   |
| `PaymentSucceeded`     | Sikeres fizetés (a tranzakció végbement)                            |
| `PaymentFailed`        | Sikertelen fizetés (elutasított kártya, elégtelen egyenleg stb.)    |
| `PaymentCancelled`     | A vásárló megszakította a fizetést                                  |
| `PaymentTimedOut`      | A fizetés időtúllépésbe futott                                      |
| `PaymentRefunded`      | Visszatérítés (refund) történt                                      |
| `PaymentAuthorized`    | Kétlépcsős fizetés: az összeg zárolásra került (preautorizáció)     |

## IPN adatok ($event->ipn)

Minden event tartalmaz egy `$event->ipn` objektumot az alábbi tulajdonságokkal:

```php
$ipn = $event->ipn;

$ipn->orderRef;      // Megrendelési hivatkozás (string)
$ipn->transactionId; // SimplePay tranzakció azonosító (int)
$ipn->status;        // Tranzakció státusza (PaymentStatus)
$ipn->currency;      // Pénznem (Currency)
$ipn->total;         // Tranzakció összege (float)
$ipn->finishDate;    // Tranzakció befejezésének dátuma (string)
$ipn->paymentMethod; // Fizetési módszer (string, pl. "CARD")
$ipn->cardMask;      // Maszkolt kártyaszám (string|null, pl. "1234-xxxx-xxxx-5678")
```

## Használat az IpnReceived általános event-tel

Az `IpnReceived` event minden IPN értesítésnél lefut, függetlenül a tranzakció státuszától. Hasznos logolásra vagy általános feldolgozásra:

```php
// app/Listeners/LogIpnNotification.php
namespace App\Listeners;

use Illuminate\Support\Facades\Log;
use Netipar\SimplePay\Events\IpnReceived;

class LogIpnNotification
{
    public function handle(IpnReceived $event): void
    {
        Log::channel('simplepay')->info('IPN értesítés érkezett', [
            'orderRef' => $event->ipn->orderRef,
            'transactionId' => $event->ipn->transactionId,
            'status' => $event->ipn->status,
            'total' => $event->ipn->total,
            'currency' => $event->ipn->currency,
        ]);
    }
}
```

## Fontos tudnivalók

- Az IPN értesítés szerver-szerver kommunikáció, így a vásárló böngészőjétől függetlenül megbízhatóan megjön.
- A package automatikusan ellenőrzi az IPN aláírást, így biztonságosan kezelheted az adatokat.
- Az IPN-t a fizetési állapot végleges visszaigazolására használd, ne a back URL-t.
- Győződj meg róla, hogy a szervered tűzfalbeállításai engedélyezik a SimplePay IP-címeiről érkező kéréseket.
