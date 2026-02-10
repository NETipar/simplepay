# Starting a Payment

Start a payment transaction using `SimplePay::payment()->start()`. This method sends the payment initiation request to SimplePay and returns a response containing the payment URL where the customer should be redirected.

## Basic Usage

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
    customerEmail: 'customer@example.com',
    language: 'HU',
    url: route('simplepay.back'),
    methods: [PaymentMethod::CARD],
    invoice: new Address(
        name: 'Test User',
        country: 'hu',
        city: 'Budapest',
        zip: '1111',
        address: 'Test Street 1',
    ),
    items: [
        new Item(title: 'Product 1', price: 1500, quantity: 1),
        new Item(title: 'Product 2', price: 1000, quantity: 1),
    ],
));

// Redirect the customer to the payment page
return redirect($response->paymentUrl);
```

## Response Properties

| Property | Type | Description |
|---|---|---|
| `$response->transactionId` | `int` | SimplePay transaction ID |
| `$response->paymentUrl` | `string` | URL to redirect the customer to |
| `$response->timeout` | `string` | Payment timeout |

## Two-Step Payment (Preauthorization)

Use `twoStep: true` to authorize the payment without capturing it immediately. You can capture it later using the `finish()` method.

```php
$response = SimplePay::payment()->start(new PaymentRequest(
    currency: Currency::HUF,
    total: 5000,
    orderRef: 'ORDER-PREAUTH-001',
    customerEmail: 'customer@example.com',
    language: 'HU',
    url: route('simplepay.back'),
    methods: [PaymentMethod::CARD],
    twoStep: true,
    invoice: new Address(
        name: 'Test User',
        country: 'hu',
        city: 'Budapest',
        zip: '1111',
        address: 'Test Street 1',
    ),
    items: [
        new Item(title: 'Reservation deposit', price: 5000, quantity: 1),
    ],
));

return redirect($response->paymentUrl);
```

## Card Storage (OneClick / Recurring)

To store the customer's card for future payments, include the `recurring` parameter with a token and expiry.

```php
$response = SimplePay::payment()->start(new PaymentRequest(
    currency: Currency::HUF,
    total: 3000,
    orderRef: 'ORDER-REC-001',
    customerEmail: 'customer@example.com',
    language: 'HU',
    url: route('simplepay.back'),
    methods: [PaymentMethod::CARD],
    recurring: [
        'times' => 3,
        'until' => '2025-12-31T00:00:00+02:00',
        'maxAmount' => 10000,
    ],
    invoice: new Address(
        name: 'Test User',
        country: 'hu',
        city: 'Budapest',
        zip: '1111',
        address: 'Test Street 1',
    ),
    items: [
        new Item(title: 'Monthly subscription', price: 3000, quantity: 1),
    ],
));

return redirect($response->paymentUrl);
```

### Recurring Parameters

| Parameter    | Description                                           |
|-------------|-------------------------------------------------------|
| `times`     | Number of tokens to generate (min `1`)                |
| `until`     | Expiry date for the stored card authorization         |
| `maxAmount` | Maximum amount per charge                             |

## Optional Fields

You can provide additional optional fields for a more complete payment request.

```php
$response = SimplePay::payment()->start(new PaymentRequest(
    currency: Currency::HUF,
    total: 4500,
    orderRef: 'ORDER-FULL-001',
    customerEmail: 'customer@example.com',
    language: 'HU',
    url: route('simplepay.back'),
    methods: [PaymentMethod::CARD],
    customer: 'John Doe',
    invoice: new Address(
        name: 'John Doe',
        country: 'hu',
        city: 'Budapest',
        zip: '1111',
        address: 'Invoice Street 1',
    ),
    delivery: new Address(
        name: 'John Doe',
        country: 'hu',
        city: 'Budapest',
        zip: '1222',
        address: 'Delivery Street 5',
    ),
    shippingPrice: 500,
    discount: 200,
    items: [
        new Item(title: 'Product A', price: 3200, quantity: 1),
        new Item(title: 'Product B', price: 1000, quantity: 1),
    ],
));

return redirect($response->paymentUrl);
```

## Available Payment Methods

| Enum | Description |
|---|---|
| `PaymentMethod::CARD` | Bank card payment |
| `PaymentMethod::WIRE` | Wire transfer |

## Available Currencies

| Enum | Description |
|---|---|
| `Currency::HUF` | Hungarian Forint |
| `Currency::EUR` | Euro |
| `Currency::USD` | US Dollar |
