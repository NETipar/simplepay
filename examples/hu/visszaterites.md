# Back URL kezelése

A package automatikusan regisztrálja a `GET /simplepay/back` route-ot. Miután a vásárló befejezi (vagy megszakítja) a fizetést a SimplePay oldalán, a rendszer visszairányítja erre az URL-re, és a package feldolgozza a választ.

## Automatikus route regisztráció

A package automatikusan regisztrálja a back URL-t:

```
GET /simplepay/back
```

Nem szükséges külön route-ot felvenni. A package kezeli az aláírás ellenőrzését és a `BackResponse` objektum létrehozását.

## Response testreszabása (Fortify-stílus)

A visszairányítás logikáját az `AppServiceProvider`-ben szabhatod testre, a `BackUrlResponse` contract felülírásával:

### 1. Hozd létre a saját Response osztályt

```php
// app/Http/Responses/SimplePayBackResponse.php
namespace App\Http\Responses;

use Illuminate\Http\Request;
use Netipar\SimplePay\Contracts\BackUrlResponse;
use Netipar\SimplePay\Dto\BackResponse;
use Netipar\SimplePay\Enums\BackEvent;
use Symfony\Component\HttpFoundation\Response;

class SimplePayBackResponse implements BackUrlResponse
{
    public function toResponse(Request $request, BackResponse $back): Response
    {
        return match ($back->event) {
            BackEvent::Success => redirect()->route('payment.success', ['order' => $back->orderRef]),
            BackEvent::Fail    => redirect()->route('payment.failed'),
            BackEvent::Cancel  => redirect()->route('payment.cancelled'),
            BackEvent::Timeout => redirect()->route('payment.timeout'),
        };
    }
}
```

### 2. Regisztráld az AppServiceProvider-ben

```php
// app/Providers/AppServiceProvider.php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Netipar\SimplePay\Contracts\BackUrlResponse;
use App\Http\Responses\SimplePayBackResponse;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(BackUrlResponse::class, SimplePayBackResponse::class);
    }
}
```

## Válasz objektum

A `BackResponse` objektum az alábbi tulajdonságokat tartalmazza:

```php
$back->event;         // BackEvent enum (Success, Fail, Cancel, Timeout)
$back->transactionId; // SimplePay tranzakció azonosító (int)
$back->orderRef;      // Az eredeti megrendelési hivatkozás (string)
$back->merchant;      // Kereskedői azonosító (string)
$back->total;         // A fizetés összege (float)
```

## Események részletesen

| Esemény              | Leírás                                                       |
|----------------------|--------------------------------------------------------------|
| `BackEvent::Success` | Sikeres fizetés, a tranzakció végbement                      |
| `BackEvent::Fail`    | A fizetés sikertelen volt (pl. elutasított kártya)           |
| `BackEvent::Cancel`  | A vásárló megszakította a fizetést                           |
| `BackEvent::Timeout` | A fizetés időtúllépésbe futott, a vásárló nem fejezte be     |

## Példa: összetett kezelés

```php
namespace App\Http\Responses;

use Illuminate\Http\Request;
use Netipar\SimplePay\Contracts\BackUrlResponse;
use Netipar\SimplePay\Dto\BackResponse;
use Netipar\SimplePay\Enums\BackEvent;
use Symfony\Component\HttpFoundation\Response;

class SimplePayBackResponse implements BackUrlResponse
{
    public function toResponse(Request $request, BackResponse $back): Response
    {
        return match ($back->event) {
            BackEvent::Success => $this->handleSuccess($back),
            BackEvent::Fail    => $this->handleFailure($back),
            BackEvent::Cancel  => $this->handleCancel($back),
            BackEvent::Timeout => $this->handleTimeout($back),
        };
    }

    private function handleSuccess(BackResponse $back): Response
    {
        return response()->view('payment.success', [
            'orderRef' => $back->orderRef,
            'transactionId' => $back->transactionId,
            'total' => $back->total,
        ]);
    }

    private function handleFailure(BackResponse $back): Response
    {
        return response()->view('payment.failed', [
            'orderRef' => $back->orderRef,
        ]);
    }

    private function handleCancel(BackResponse $back): Response
    {
        return redirect()
            ->route('checkout')
            ->with('warning', 'A fizetés megszakításra került.');
    }

    private function handleTimeout(BackResponse $back): Response
    {
        return response()->view('payment.timeout', [
            'orderRef' => $back->orderRef,
        ]);
    }
}
```

## Fontos tudnivalók

- A back URL kezelése NEM jelenti a fizetés végleges visszaigazolását. A végleges visszaigazolás az IPN webhook-on keresztül érkezik (lásd: [IPN webhook kezelése](ipn-webhook.md)).
- A back URL csak tájékoztat a fizetés állapotáról, és a vásárlói felületet irányítja.
- A `BackResponse` automatikusan ellenőrzi az `s` paraméter aláírásának érvényességét.
- Ha nem regisztrálsz saját `BackUrlResponse` implementációt, a default válasz a `/` útvonalra irányít.
