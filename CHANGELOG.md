# Changelog

All notable changes to `netipar/simplepay` will be documented in this file.

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
