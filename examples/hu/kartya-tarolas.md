# Kártyatárolás (OneClick, Recurring)

A `SimplePay::cardStorage()` metódusain keresztül kezelheted a tárolt kártyákat, OneClick és ismétlődő (recurring) fizetéseket.

## OneClick fizetés tárolt kártyával

Ha a vásárló korábban hozzájárult a kártyájának tárolásához (lásd: [Fizetés indítás - kártyatárolással](fizetes-inditas.md)), a `do()` metódussal indíthatod a fizetést a tárolt kártyával:

```php
use Netipar\SimplePay\Facades\SimplePay;
use Netipar\SimplePay\Enums\Currency;
use Netipar\SimplePay\Dto\CardStorageDoRequest;

$result = SimplePay::cardStorage()->do(new CardStorageDoRequest(
    currency: Currency::HUF,
    transactionId: null, // elavult: nem kerül a kérésbe, a következő major verzióban megszűnik
    cardId: 'card-abc-123',
    orderRef: 'ORDER-456',
    total: 3000,
    customerEmail: 'vasarlo@example.com',
    url: route('simplepay.back'), // ide tér vissza a vásárló 3DS challenge után
    cardSecret: $cardSecret,      // a kártya tárolásakor megadott titok
    threeDSReqAuthMethod: '02',   // 01 vendég, 02 regisztrált, 05 harmadik feles belépés
    browser: $browser,            // lásd lent: „Böngészőadatok a 3DS-hez”
));
```

### Böngészőadatok a 3DS-hez

A OneClick és az AutoPayment fizetés a vásárló böngészőadatait is elküldi a kártyakibocsátó 3DS-ellenőrzéséhez. A fizetési oldalon gyűjtsd be a `navigator.language`, a `screen.colorDepth`, a `screen.height`, a `screen.width` és a `new Date().getTimezoneOffset()` értékét, a szerveren pedig add hozzá a kérés fejléceit és az IP-címet:

```php
use Netipar\SimplePay\Dto\BrowserData;

$browser = new BrowserData(
    accept: (string) $request->header('Accept'),
    agent: (string) $request->userAgent(),
    language: $request->input('browser.lang'),
    javaEnabled: false,
    colorDepth: (int) $request->input('browser.color'),
    screenHeight: (int) $request->input('browser.height'),
    screenWidth: (int) $request->input('browser.width'),
    timeZone: (int) $request->input('browser.tz'),
    ip: $request->ip(),
);
```

Böngészőadatok nélkül a 3DS-ellenőrzés hibával zárulhat. Ha a kibocsátó challenge-et kér, az API `3004`-es hibakóddal válaszol; ilyenkor a vásárlót a `$e->getResponseData()['redirectUrl']` címre kell irányítani.

## Ismétlődő fizetés (recurring)

Az ismétlődő fizetés (MIT - Merchant Initiated Transaction) lehetővé teszi, hogy a kereskedői oldalról indítsunk fizetést a vásárló közvetlen közreműködése nélkül:

```php
use Netipar\SimplePay\Dto\RecurringRequest;
use Netipar\SimplePay\Enums\TransactionType;

$result = SimplePay::cardStorage()->doRecurring(new RecurringRequest(
    currency: Currency::HUF,
    transactionId: null, // elavult: nem kerül a kérésbe, a következő major verzióban megszűnik
    orderRef: 'ORDER-REC-001',
    total: 5000,
    customerEmail: 'vasarlo@example.com',
    url: route('simplepay.back'),
    type: TransactionType::Mit,
    token: $recurringToken, // kötelező: a kártyaregisztráció egyik tokenje
));
```

A `token` nélküli ismétlődő fizetés még a kérés elküldése előtt `InvalidArgumentException`-t dob.

### Tranzakció típusok

| Típus                   | Leírás                                                        |
|-------------------------|---------------------------------------------------------------|
| `TransactionType::Mit`  | Merchant Initiated Transaction - kereskedői kezdeményezés     |
| `TransactionType::Cit`  | Customer Initiated Transaction - vásárlói kezdeményezés       |

## Kártya lekérdezés

Tárolt kártya adatainak lekérdezése:

```php
// Alap kártya lekérdezés
$card = SimplePay::cardStorage()->cardQuery(Currency::HUF, 'card-abc-123');

// Kártya lekérdezés előzményekkel (tranzakció történelem)
$cardWithHistory = SimplePay::cardStorage()->cardQuery(
    Currency::HUF,
    'card-abc-123',
    history: true,
);
```

## Kártya törlés

Tárolt kártya eltávolítása a rendszerből:

```php
SimplePay::cardStorage()->cardCancel(Currency::HUF, 'card-abc-123');
```

## Token lekérdezés

Token adatainak lekérdezése:

```php
SimplePay::cardStorage()->tokenQuery(Currency::HUF, 'token-xyz');
```

## Token törlés

Token eltávolítása a rendszerből:

```php
SimplePay::cardStorage()->tokenCancel(Currency::HUF, 'token-xyz');
```

## Teljes folyamat összefoglalása

A kártyatárolással történő fizetés teljes folyamata a következő:

1. **Első fizetés** - A vásárló fizet és hozzájárul a kártya tárolásához (`recurring` paraméter megadásával a `start()` hívásban).
2. **Kártya tárolása** - A SimplePay tárolja a kártyát, és visszaküldi a `cardId`-t és/vagy `token`-t az IPN-ben.
3. **Következő fizetések** - A tárolt `cardId`-vel vagy `token`-nel indíthatod a következő fizetéseket (`do()` vagy `doRecurring()`).
4. **Kártya törlés** - Amikor a vásárló kérelmezi, törölheted a tárolt kártyát (`cardCancel()`).

## Összefoglaló

| Művelet              | Metódus          | Leírás                                          |
|----------------------|------------------|--------------------------------------------------|
| OneClick fizetés     | `do()`           | Fizetés tárolt kártyával (vásárlói beleegyezés)  |
| Ismétlődő fizetés    | `doRecurring()`  | Kereskedői kezdeményezésű fizetés                |
| Kártya lekérdezés    | `cardQuery()`    | Tárolt kártya adatai és előzményei               |
| Kártya törlés        | `cardCancel()`   | Tárolt kártya eltávolítása                       |
| Token lekérdezés     | `tokenQuery()`   | Token adatainak lekérdezése                      |
| Token törlés         | `tokenCancel()`  | Token eltávolítása                               |
