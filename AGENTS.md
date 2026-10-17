# SimplePay for Laravel

This repository is a Laravel package. Keep the package focused, idiomatic, and easy for Laravel developers to install, test, and maintain.

## Package Conventions

- Use Laravel-native package APIs and the existing service provider shape before adding abstractions.
- Keep package names, namespaces, Composer metadata, publish tags, documentation, and examples aligned with `netipar/simplepay`.
- Add only the files and dependencies needed for the package behavior being implemented.
- Prefer explicit Laravel package code over helper abstractions unless the extension point is real.
- Keep tests focused on observable package behavior through public APIs, service provider wiring, commands, routes, published resources, and documentation promises.

## Quick Commands

- Full validation: `composer test`
- Formatting check: `composer lint:check`
- Static analysis: `composer analyse`
- Pest tests: `composer test:unit`
- Workbench build: `composer build`
- Workbench server: `composer serve`

## Local Skills

- `package-scaffold`: use when adding package capabilities or wiring them through the service provider, including commands, migrations, routes, config, views, translations, assets, middleware, publish tags, workbench files, and console-only behavior.
- `package-testing`: use when adding or changing package tests with Pest 4/5 and Orchestra Testbench.
- `package-release`: use when preparing changelog, release notes, tags, or GitHub release workflow changes.
- `package-compatibility`: use when reviewing code, dependencies, or CI against the PHP and Laravel support matrix.
- `package-generate-skill`: use when updating the bundled Boost skill from the package implementation, README, and examples.

## SimplePay Architecture

- The package follows the official SimplePay PHP SDKs (API v2, published at https://simplepay.hu/fejlesztoknek/). `official-docs/` holds the reference PDFs; it is not part of the dist archive.
- `src/SimplePay.php` sits behind the `SimplePay` facade and exposes the services in `src/Services`: `PaymentService` (start, finish, refund, query, cancel, qvik/EAM, Apple Pay, merchant-side Google Pay with `doGooglePay()` and `googlePayConfig()`), `CardStorageService` (OneClick `do`, recurring, card and token query/cancel), `AutoPaymentService` (merchant-side card data) and `RtpService` (Request to Pay).
- Request and response payloads are readonly DTOs in `src/Dto`: `toArray()` builds the API body including `salt` and `sdkVersion`, `fromArray()` maps the response. Enums live in `src/Enums`; `ErrorCode` mirrors the official error code list with Hungarian descriptions.
- `src/Support/Client.php` signs every request with HMAC-SHA384 (`Support/Signature`), verifies the response signature and throws `SimplePayApiException` when `errorCodes` is not empty; the exception keeps the response body (`getResponseData()`), e.g. the `redirectUrl` of a 3DS challenge (3004). `doGooglePay()` turns a 3004 with a `redirectUrl` into a normal `GooglePayResponse`.
- Merchant credentials are configured per currency (`simplepay.merchants`) and resolved by `Support/MerchantResolver`; IPN bodies without `currency` are resolved by their `merchant` field.
- Routes: `POST simplepay/ipn` (`VerifySimplePaySignature` middleware; `SimplePayWebhookController` dispatches the events in `src/Events`) and `GET simplepay/back` (`SimplePayBackController`, response customisable through the `BackUrlResponse` contract).
- Consumer documentation is bilingual: keep `examples/en/` and `examples/hu/` in sync with each other and with the README.
