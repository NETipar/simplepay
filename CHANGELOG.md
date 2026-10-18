# Release Notes

## [Unreleased](https://github.com/NETipar/simplepay/compare/v2.1.0...HEAD)

## [v2.1.0](https://github.com/NETipar/simplepay/compare/v2.0.0...v2.1.0) - 2026-10-18

### Fixed

- Requests now send the fields of the official SimplePay PHP SDK samples. Several calls could not work before; no action is needed unless noted below.
  - Every request sends `sdkVersion`; card and token queries and cancels send the currency.
  - `PaymentRequest` sends the shipping cost as `shippingCost`, so SimplePay adds it to the amount to pay (it was sent as `shippingPrice` and ignored), and `Item` sends its description as `desc`.
  - OneClick (`CardStorageDoRequest`): new optional `cardSecret`, `type`, `threeDSReqAuthMethod`, `browser`, `customer` and `invoice`; `transactionId` is deprecated and no longer sent, because the card is identified by `cardId` (the transaction ID of the card registration).
  - Recurring (`RecurringRequest`): new `token` parameter, which the `dorecurring` call requires; `transactionId` is deprecated and no longer sent. Action: pass `token`; a request without it now throws `InvalidArgumentException` before it is sent.
  - Auto payment (`AutoPaymentRequest`): the browser data is sent as `browser` with the official keys (`BrowserData` gained an optional `ip`), plus new optional `url`, `threeDSReqAuthMethod` and `language`.
  - Request to Pay: `RtpStartRequest` sends `language`, `deadline` (default 2 hours 5 minutes, or `timeout` seconds) and optional `additionalInfo`, and no longer sends `timeout`; batches send `packageRef` (generated when missing) and `transactions`; `query()` sends id lists and accepts `packageRef`; `refund()` sends `refundTotal` and the currency, with optional `originalTotal`; `reverse()` sends the currency.
  - Apple Pay: `ApplePayStartRequest` sends `domain` (default: the host of `app.url`), `language` and `timeout`. Action: pass `domain` when the checkout does not run on the host of `APP_URL`; when no domain can be resolved, it throws `InvalidArgumentException`. `ApplePayDoRequest` sends the token as `applePayToken`.
  - qvik (`EamStartRequest`): sends `eamType` (default 1, QR), `language` and `timeout`.
  
- The debug log (`SIMPLEPAY_LOG_CHANNEL`) no longer contains card numbers, expiry dates, CVCs, card secrets or tokens.

### Added

- `PaymentResponse::$tokens`: the recurring tokens of a card registration start.

### Changed

- `CardStorageDoRequest::$transactionId` and `RecurringRequest::$transactionId` are `?int`; they are deprecated and will be removed in the next major version.

### Documentation

- The README, the examples and the Boost skill match the package API (`FinishRequest`, `RefundRequest`, IPN events, card storage, Request to Pay, Apple Pay), and the code comments of the Hungarian examples are in English.

## [v2.0.0](https://github.com/NETipar/simplepay/compare/v1.2.0...v2.0.0) - 2026-10-17

### Breaking Changes

- Requires PHP 8.3+ and Laravel 12 or 13; PHP 8.2 and Laravel 11 are no longer supported. Action: upgrade PHP and Laravel before updating the package.
- `PaymentResponse::$timeout` is now `?string`, because Request to Pay start responses have no timeout. Action: handle `null` where you read `$response->timeout`.
- New `BackEvent::Pending` case: Request to Pay back redirects carry it while the bank transfer is under way. Action: cover `BackEvent::Pending` in `match ($back->event)` expressions without a `default` arm.

### Added

- Laravel 13 support.
- Google Pay on your own checkout page: `SimplePay::payment()->doGooglePay()` charges the Google Pay token (a 3DS challenge returns a `redirectUrl` instead of throwing), and `SimplePay::payment()->googlePayConfig()` gives the values of the Google Pay button. Set `SIMPLEPAY_GOOGLE_PAY_MERCHANT_ID` and `SIMPLEPAY_GOOGLE_PAY_MERCHANT_NAME` to use it. See `examples/en/google-pay.md`.
- Event-specific redirect URLs: `PaymentRequest` and `RtpStartRequest` accept `urls: new RedirectUrls(...)`, including `pending` for Request to Pay.
- `SimplePayApiException::getResponseData()` returns the full API response, e.g. the `redirectUrl` of a 3DS challenge (error code 3004) in card storage and auto payments.
- `php artisan vendor:publish --tag=simplepay` publishes the config and the logos at once.
- SVG version of the vertical SimplePay logo.

### Fixed

- Request to Pay back redirects with the `PENDING` event no longer fail with a `ValueError`.
- Request to Pay starts no longer fail with a `TypeError` on the missing `timeout`.
- Start responses with a numeric string `transactionId` are accepted.

## v1.2.0 - 2026-04-28

### Changed

- `IpnMessage::$currency` and `IpnMessage::$total` are now nullable to support SimplePay v2 IPN payloads which omit these fields.
- `SimplePayWebhookController` and `PaymentService::handleIpn()` now resolve the response signature secret key via the `merchant` field when `currency` is not present in the IPN body.

### Added

- Test coverage for SimplePay v2 IPN payloads without `currency` and `total` fields.

### Notes

- Consumers reading `$ipn->currency` or `$ipn->total` should now handle `null` values.

## v1.1.0 - 2026-04-28

### Changed

- IPN signature middleware no longer requires the `currency` field in the request body. When `currency` is missing, the secret key is resolved via the `merchant` field instead.

### Added

- `MerchantResolver::getSecretKeyByMerchant(string $merchant): string` for resolving the secret key by merchant ID.

## v1.0.2 - 2026-03-02

### Added

- Laravel Boost development skill for AI-assisted SimplePay integration

## v1.0.1 - 2026-03-02

### Added

- GitHub Actions CI workflow with Pint and Pest across PHP 8.2/8.3/8.4 + Laravel 11/12 matrix
- Publishable SimplePay logo assets

## v1.0.0 - 2026-03-02

Initial release based on the official SimplePay PHP SDK v2.1.

### Features

- Start payment transactions with redirect to SimplePay
- Two-step payments (authorize + finish)
- Refund transactions (partial and full)
- Query transaction status
- Cancel transactions
- EAM / Qvik payment support
- Apple Pay (start + complete)
- Card storage: OneClick payments with stored cards
- Card storage: Recurring (tokenized) payments
- Card/token query and cancellation
- Auto payment (PCI-DSS compliant direct card payment)
- Request to Pay (RTP): start, batch, query, refund, reverse
- IPN webhook with automatic route registration and signature verification
- Back URL handling with customizable response via `BackUrlResponse` contract
- Event-driven architecture: `PaymentSucceeded`, `PaymentFailed`, `PaymentCancelled`, `PaymentTimedOut`, `PaymentAuthorized`, `PaymentRefunded`, `IpnReceived`

### Architecture

- PHP 8.2+ typed DTOs, enums, constructor promotion
- Laravel HTTP Client with HMAC-SHA384 signature verification
- Multi-currency merchant support (HUF, EUR, USD)
- Full enum support: `Currency`, `PaymentMethod`, `PaymentStatus`, `BackEvent`, `TransactionType`, `ErrorCode` (358+ codes)
- Structured exception handling with `SimplePayApiException`
- Configurable logging, timeout, sandbox mode, 3DS auto-challenge
- 66 tests, 165 assertions
