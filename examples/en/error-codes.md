# SimplePay Error Codes

Complete list of error codes returned by the SimplePay API. Error codes are included in the `SimplePayApiException` exception and can be handled programmatically using the `ErrorCode` enum.

```php
use Netipar\SimplePay\Exceptions\SimplePayApiException;
use Netipar\SimplePay\Enums\ErrorCode;

try {
    $response = SimplePay::payment()->start($request);
} catch (SimplePayApiException $e) {
    $e->getMessage();       // "[5083] Token times szükséges"
    $e->getErrorCodes();    // [5083]
    $e->hasErrorCode(5083); // true
    $e->getResolvedCodes(); // [ErrorCode::TokenTimesRequired]
}
```

## General Errors

| Code | Enum | Description |
|------|------|-------------|
| 999 | `GeneralError` | General error |
| 1529 | `InternalError` | SimplePay internal error |
| 2999 | `InternalError2` | SimplePay internal error |

## Payment & Card Errors (2xxx)

| Code | Enum | Description |
|------|------|-------------|
| 2003 | `InvalidPassword` | Invalid password |
| 2004 | `GeneralError2` | General error |
| 2006 | `MerchantNotFound` | Merchant not found |
| 2008 | `InvalidEmail` | Invalid email address |
| 2010 | `InvalidTransactionId` | Invalid transaction ID |
| 2013 | `InsufficientFunds` | Insufficient funds on card |
| 2014 | `PasswordRequired` | Password required for payment |
| 2016 | `PaymentCancelledByUser` | Payment cancelled by user |
| 2019 | `AcquirerTimeout` | Acquirer communication timeout |
| 2020 | `AcquirerError` | Acquirer bank error |
| 2021 | `Interactive3dsRequired` | Card issuer requires interactive 3DS verification |
| 2023 | `Repeated3dsFailed` | Repeated 3DS verification also failed |
| 2030 | `CardNotDeletableOrInvalidAmount` | Card not deletable (positive balance) / Invalid amount |
| 2040 | `InvalidCurrency` | Invalid currency |
| 2063 | `CardInactive` | Card inactive |
| 2064 | `InvalidCardData` | Invalid card data |
| 2066 | `CardNotChargeableOrLimitExceeded` | Card not chargeable / limit exceeded |
| 2068 | `CardDataErrorOrNotExist` | Card data error / card does not exist |
| 2071 | `InvalidCardDataOrNotExist` | Invalid card data / card does not exist |
| 2072 | `InvalidCardDataCheckRequired` | Invalid card data, verification required |
| 2073 | `InvalidCardDataCheckRequired2` | Invalid card data, verification required |
| 2074 | `CardholderNameTooLong` | Cardholder name exceeds 32 characters |
| 2077 | `InvalidCvc` | Invalid CVC |
| 2078 | `GeneralCardError` | General error, card issuer does not disclose the reason |
| 2079 | `AcquirerNotAvailable` | Acquirer bank not available for routing |
| 2130 | `InvalidInputCode` | Invalid input code |
| 2131 | `InvalidAddress` | Invalid address |
| 2132 | `InvalidBirthDate` | Invalid birth date |

## 3DS Errors (3xxx)

| Code | Enum | Description |
|------|------|-------------|
| 3000 | `General3dsError` | General 3DS error |
| 3001 | `Invalid3dsResponse` | Invalid 3DS response message |
| 3002 | `ThreeDsProcessError` | 3DS process error |
| 3003 | `ThreeDsProcessError2` | 3DS process error |
| 3004 | `ThreeDsChallengeRedirect` | Redirect during 3DS challenge |
| 3005 | `ThreeDsInteractiveAuthRequired` | 3DS interactive authentication required |
| 3006 | `ThreeDsCardAssociationTimeout` | 3DS error - Card association response missing or delayed |
| 3012 | `ThreeDsNotCapableCard` | 3DS error, card not 3DS capable |
| 3013 | `ThreeDsProcessError3` | 3DS process error |
| 3102 | `Invalid3dsVersion` | Invalid 3DS version |
| 3203 | `ThreeDsDataFormatError` | 3DS data format error or missing required data |
| 3303 | `ThreeDsInternalError` | 3DS internal error, invalid 3DS endpoint |
| 3402 | `ThreeDsVerificationTimeout` | 3DS verification process timeout |
| 3403 | `ThreeDsTemporaryError` | 3DS temporary system error |
| 3404 | `ThreeDsPermanentError` | 3DS permanent system error |
| 3501 | `ThreeDsAcsCancelledByUser` | 3DS ACS - user cancelled interactive authentication |
| 3504 | `ThreeDsAcsTimeout` | 3DS ACS timeout, card issuer side |
| 3505 | `ThreeDsAcsNoResponse` | 3DS ACS timeout, no response from card issuer |
| 3506 | `ThreeDsAcsTransactionError` | 3DS ACS transaction error, card issuer side |
| 3507 | `ThreeDsAcsUnknownError` | 3DS ACS unknown error, card issuer side |
| 3508 | `ThreeDsAcsComponentError` | 3DS ACS transaction error in card issuer component |

## API Errors (5xxx)

### General API Errors

| Code | Enum | Description |
|------|------|-------------|
| 5000 | `ApiGeneralError` | General error |
| 5010 | `MerchantAccountNotFound` | Merchant account not found |
| 5011 | `TransactionNotFound` | Transaction not found |
| 5012 | `MerchantAccountMismatch` | Merchant account mismatch |
| 5013 | `TransactionAlreadyExists` | Transaction already exists |
| 5014 | `InvalidTransactionType` | Invalid transaction type |
| 5015 | `TransactionInPayment` | Transaction is in payment |
| 5016 | `TransactionTimeout` | Transaction timeout |
| 5017 | `TransactionAborted` | Transaction aborted |
| 5018 | `TransactionAlreadyPaid` | Transaction already paid |
| 5019 | `InvalidTransactionIdentifier` | Invalid transaction identifier |
| 5020 | `OriginalTotalCheckFailed` | Original transaction total check failed |
| 5021 | `TransactionAlreadyFinished` | Transaction already finished |
| 5022 | `TransactionInvalidState` | Transaction not in expected state for the request |
| 5023 | `UnknownCurrency` | Unknown / invalid account currency |
| 5024 | `InvalidEmailAddress` | Invalid email address |
| 5025 | `InvalidName` | Invalid name |
| 5026 | `TransactionBlockedByFraud` | Transaction blocked (failed fraud check) |
| 5027 | `TransactionLockError` | Transaction lock error |
| 5028 | `TransactionNotReconciled` | Transaction not reconciled |
| 5029 | `TransactionNotRefunded` | Transaction not yet refunded |
| 5030 | `OperationNotAllowed` | Operation not allowed |
| 5033 | `RefundCancelNotAllowed` | Refund transaction cancellation not allowed |

### Stored Card & Recurring Errors

| Code | Enum | Description |
|------|------|-------------|
| 5040 | `StoredCardNotFound` | Stored card not found |
| 5041 | `StoredCardExpired` | Stored card expired |
| 5042 | `StoredCardDeactivated` | Stored card deactivated |
| 5043 | `PhantomStoredCard` | Phantom stored card |
| 5044 | `RecurringNotEnabled` | Recurring not enabled |
| 5045 | `RecurringRemoteIdRequired` | Remote ID required for recurring registration |
| 5046 | `RecurringIdtsRequired` | IDTS required for recurring registration |
| 5047 | `RecurringDuplicateIdts` | Duplicate IDTS |
| 5048 | `RecurringUntilRequired` | Recurring until required |
| 5049 | `RecurringUntilMismatch` | Recurring until mismatch |
| 5071 | `StoredCardInvalidLength` | Stored card invalid length |
| 5072 | `StoredCardInvalidOperation` | Stored card invalid operation |
| 5080 | `OriginalIdMismatchOnRecurring` | Original ID mismatch on recurring registration |
| 5081 | `RecurringTokenNotFound` | Recurring token not found |
| 5082 | `RecurringTokenInUse` | Recurring token in use |
| 5083 | `TokenTimesRequired` | Token times required |
| 5084 | `TokenTimesTooLarge` | Token times too large |
| 5085 | `TokenUntilRequired` | Token until required |
| 5086 | `TokenUntilTooLarge` | Token until too large |
| 5087 | `TokenMaxAmountRequired` | Token maxAmount required |
| 5088 | `TokenMaxAmountTooLarge` | Token maxAmount too large |
| 5089 | `RecurringAndOneclickConflict` | Recurring and oneclick registration cannot start simultaneously |
| 5090 | `RecurringTokenRequired` | Recurring token required |
| 5091 | `RecurringTokenInactive` | Recurring token inactive |
| 5092 | `RecurringTokenExpired` | Recurring token expired |
| 5093 | `RecurringAccountMismatch` | Recurring account mismatch |
| 5094 | `InvalidTimeoutDefinition` | Invalid timeout definition |
| 5095 | `PreselectedMethodRequired` | Pre-selected payment method required |
| 5096 | `EamNotEnabled` | EAM not enabled |
| 5097 | `InvalidEamType` | Invalid EAM type |

### Validation Errors

| Code | Enum | Description |
|------|------|-------------|
| 5050 | `InvalidPaymentMethod` | Invalid payment method |
| 5051 | `InvalidReferenceNumber` | Invalid reference number |
| 5052 | `InvalidExternalReference` | Invalid external reference |
| 5053 | `InvalidTimestamp` | Invalid timestamp |
| 5054 | `InvalidBillingFirstName` | Invalid billing first name |
| 5055 | `InvalidBillingLastName` | Invalid billing last name |
| 5056 | `InvalidBillingEmail` | Invalid billing email |
| 5057 | `InvalidBillingPhone` | Invalid billing phone |
| 5058 | `InvalidBillingAddress` | Invalid billing address |
| 5059 | `InvalidBillingCity` | Invalid billing city |
| 5060 | `InvalidShippingFirstName` | Invalid shipping first name |
| 5061 | `InvalidShippingLastName` | Invalid shipping last name |
| 5062 | `InvalidShippingPhone` | Invalid shipping phone |
| 5063 | `InvalidShippingAddress` | Invalid shipping address |
| 5064 | `InvalidShippingCity` | Invalid shipping city |
| 5065 | `InvalidAmount` | Invalid amount |
| 5066 | `InvalidCurrencyCode` | Invalid currency code |
| 5067 | `MissingBillingEmail` | Required billing email missing |
| 5068 | `InvalidShippingEmail` | Invalid shipping email |
| 5069 | `MaxAmountExceeded` | Maximum allowed amount exceeded |
| 5070 | `DirectPaymentCreationError` | Error creating new direct payment |

### Signature & Request Errors

| Code | Enum | Description |
|------|------|-------------|
| 5100 | `InvalidSignature` | Invalid signature |
| 5101 | `MissingOrderRef` | Missing order reference |
| 5102 | `MissingOrderAmount` | Missing order amount |
| 5103 | `MissingOrderCurrency` | Missing order currency |
| 5104 | `MissingDate` | Missing date |
| 5105 | `ConfirmationError` | Confirmation error |
| 5106 | `InvalidRefundAmount` | Invalid refund amount |
| 5110 | `InvalidRefundTotal` | Invalid refund total |
| 5111 | `OrderRefOrTransactionIdRequired` | Either orderRef or transactionId is required |
| 5113 | `SdkVersionRequired` | sdkVersion is required |
| 5150 | `MissingIosMerchantId` | Missing iOS merchant ID |
| 5151 | `MissingExternalReference` | Missing external reference |
| 5199 | `InvalidCardDetails` | Invalid card details |

### Invoice & Product Errors

| Code | Enum | Description |
|------|------|-------------|
| 5200 | `InvalidInvoice` | Invalid invoice |
| 5201 | `MissingMerchantId` | Missing merchant account ID |
| 5202 | `AccessDenied` | Access denied |
| 5203 | `InvalidData` | Invalid data |
| 5204 | `InvalidProductCode` | Invalid product code |
| 5205 | `InvalidProductName` | Invalid product name |
| 5206 | `InvalidProductInfo` | Invalid product information |
| 5207 | `InvalidProductPrice` | Invalid product price |
| 5208 | `InvalidVatValue` | Invalid VAT value |
| 5209 | `InvalidQuantity` | Invalid quantity |
| 5210 | `InvalidProductVariant` | Invalid product variant |
| 5211 | `InvalidProductGroup` | Invalid product group |
| 5212 | `InvalidPrice` | Invalid price |
| 5213 | `MissingOrderRefField` | Missing merchant transaction ID |
| 5214 | `InvalidDate` | Invalid date |
| 5215 | `InvalidDiscount` | Invalid discount |
| 5216 | `InvalidShippingAmount` | Invalid shipping amount |
| 5217 | `InvalidMethod` | Invalid method |
| 5218 | `InvalidRedirectUrl` | Invalid redirect URL |
| 5219 | `MissingOrInvalidCustomerEmail` | Email missing or not in email format |
| 5220 | `InvalidLanguage` | Invalid transaction language |
| 5221 | `InvalidDiscountFormat` | Invalid discount format |
| 5222 | `InvalidShippingFormat` | Invalid shipping format |
| 5223 | `InvalidOrMissingCurrency` | Invalid or missing transaction currency |
| 5224 | `InvalidTimeLimit` | Invalid time limit |
| 5225 | `InvalidWireTransferTimeLimit` | Invalid wire transfer time limit |
| 5226 | `InvalidCommunicationState` | Invalid communication state |
| 5227 | `InvalidTimeoutSpec` | Invalid timeout specification |
| 5228 | `RefundLimitExceeded` | Refund limit exceeded |
| 5229 | `RefundDeadlineExpired` | Refund deadline expired |
| 5230 | `RefundNotAllowed` | Refund not allowed |

### Transaction Management Errors

| Code | Enum | Description |
|------|------|-------------|
| 5301 | `UserRedirectRequired` | User redirect required |
| 5302 | `InvalidRequestSignature` | Invalid signature in incoming request |
| 5303 | `InvalidRequestSignature2` | Invalid signature in incoming request |
| 5304 | `TimeoutFailedCall` | Call failed due to timeout |
| 5305 | `FailedTransactionSend` | Failed to send transaction to acquirer |
| 5306 | `FailedTransactionCreation` | Failed transaction creation |
| 5307 | `CurrencyMismatch` | Request currency does not match account currency |
| 5308 | `TwoStepNotEnabled` | Two-step transaction not enabled on merchant account |
| 5309 | `MissingInvoiceName` | Missing invoice recipient name |
| 5310 | `MissingInvoiceCity` | Invoice city is required |
| 5311 | `MissingInvoiceZip` | Invoice zip code is required |
| 5312 | `MissingInvoiceAddress` | Invoice address first line is required |
| 5313 | `MissingItemTitle` | Item title is required |
| 5314 | `MissingItemPrice` | Item unit price is required |
| 5315 | `MissingItemQuantity` | Item quantity is required |
| 5316 | `MissingShippingName` | Shipping recipient name is required |
| 5317 | `MissingShippingCity` | Shipping city is required |
| 5318 | `MissingShippingZip` | Shipping zip code is required |
| 5319 | `MissingShippingAddress` | Shipping address first line is required |
| 5320 | `MissingSdkVersion` | sdkVersion is required |
| 5321 | `InvalidJsonFormat` | Format error / invalid JSON string |
| 5322 | `InvalidCountry` | Invalid country |
| 5323 | `InvalidFinishAmount` | Invalid finish amount |
| 5324 | `ItemsOrTotalRequired` | Items list or transaction total required |
| 5325 | `InvalidUrl` | Invalid URL |
| 5326 | `MissingCardId` | Missing cardId |
| 5327 | `MaxOrderRefsExceeded` | Maximum number of query orderRefs exceeded |
| 5328 | `MaxTransactionIdsExceeded` | Maximum number of query transaction IDs exceeded |
| 5329 | `QueryFromAfterUntil` | Query from must precede until |
| 5330 | `QueryFromAndUntilRequired` | Query from and until must be provided together |
| 5331 | `InvalidTransactionSource` | Invalid transaction source |
| 5332 | `TransactionIdAndTargetRequired` | Transaction ID and target required |
| 5333 | `MissingTransactionId` | Missing transaction ID |
| 5334 | `CardDataRequired` | Card data required |
| 5335 | `CardBinRequired` | Card BIN required |
| 5336 | `AntiFraudHashRequired` | Anti-fraud hash required |
| 5337 | `DataSerializationError` | Error serializing complex data to text |
| 5338 | `AcquirerTransactionIdRequired` | Acquirer transaction ID required |
| 5339 | `QueryDateOrIdsRequired` | Query requires start period or identifiers |
| 5340 | `TransactionNotWalletBound` | Transaction not bound to wallet |
| 5341 | `PartnerOptionRequired` | Partner option required |
| 5342 | `PartnerAccountsRequired` | Partner accounts required |
| 5343 | `InvalidTransactionStatus` | Invalid transaction status |
| 5344 | `InvalidTransactionStatus2` | Invalid transaction status |
| 5345 | `VatAmountBelowZero` | VAT amount below zero |
| 5346 | `InvalidTransactionMode` | Invalid transaction mode |
| 5347 | `InvalidInstallmentPlan` | Invalid installment plan |
| 5348 | `JwtRequired` | JWT required |
| 5349 | `TransactionNotAllowedOnAccount` | Transaction not allowed on settlement account |
| 5350 | `InvalidEmailFormat` | Invalid email format |
| 5351 | `InvalidDay` | Invalid day |
| 5352 | `SimpleBusinessAccountError` | Simple business account error / account does not exist |
| 5353 | `InvalidStartEndParams` | Invalid start and end parameters |
| 5354 | `InvalidDeviceId` | Invalid device ID |
| 5355 | `NotSoftPosAccount` | Not a SoftPOS account |
| 5356 | `InvalidTemplate` | Invalid template |
| 5357 | `InvalidOrderRefFormat` | Invalid order reference format |
| 5358 | `InvalidPeriod` | Invalid period |
| 5359 | `SoftPosNotAvailable` | SoftPOS not available |
| 5360 | `RemoteAssignmentFailed` | Remote transaction assignment failed |
| 5361 | `RemoteDeletionFailed` | Remote transaction deletion failed |
| 5362 | `RemoteExpirationFailed` | Remote transaction expiration setting failed |
| 5363 | `OriginalTransactionRequired` | Original transaction required |
| 5364 | `InvalidAdditionalInfo` | Invalid additional information |
| 5365 | `PartnerIdRequired` | Partner ID required |
| 5366 | `InvalidEamStatus` | Invalid EAM status |
| 5367 | `PaymentReferenceRequired` | Payment reference required |
| 5368 | `DebtorBicRequired` | Debtor bank BIC code required |
| 5369 | `EndToEndIdRequired` | End-to-end ID required |
| 5370 | `InvalidItemPrice` | Invalid item price |
| 5371 | `InvalidItemQuantity` | Invalid item quantity |
| 5372 | `AmountPaidToMerchant` | Amount paid to merchant |
| 5373 | `ApplePayNotEnabled` | Apple Pay not enabled |
| 5380 | `EmailParamsRequired` | Email parameters required |
| 5381 | `EmailTypeRequired` | Email type required |
| 5382 | `CustomerEmailRequired` | Customer email required |

### Security & Session Errors

| Code | Enum | Description |
|------|------|-------------|
| 5401 | `InvalidSaltLength` | Invalid salt, not 32-64 characters long |
| 5402 | `TransactionBaseRequired` | Transaction base required |
| 5403 | `SimpleTransactionInPayment` | Simple transaction in payment |
| 5404 | `InvalidTransactionState` | Invalid transaction state |
| 5405 | `TransactionNotInCdeSession` | Transaction not in CDE session |
| 5413 | `WireTransactionCreated` | Wire transfer transaction created |

### Browser Data (3DS)

| Code | Enum | Description |
|------|------|-------------|
| 5501 | `BrowserAcceptRequired` | Browser accept header required |
| 5502 | `BrowserAgentRequired` | Browser user agent required |
| 5503 | `BrowserIpRequired` | Browser IP required |
| 5504 | `BrowserJavaRequired` | Browser Java enabled required |
| 5505 | `BrowserLanguageRequired` | Browser language required |
| 5506 | `BrowserColorRequired` | Browser color depth required |
| 5507 | `BrowserHeightRequired` | Browser height required |
| 5508 | `BrowserWidthRequired` | Browser width required |
| 5509 | `BrowserTzRequired` | Browser timezone required |
| 5530 | `InvalidType` | Invalid type |
| 5555 | `RavenCallError` | Raven call error |

### SoftPOS & Device Errors

| Code | Enum | Description |
|------|------|-------------|
| 5601 | `InvalidIdentificationCode` | Invalid identification code |
| 5602 | `InvalidMessage` | Invalid message |
| 5603 | `InvalidTransactionSituation` | Invalid transaction situation |
| 5604 | `InvalidRetailData` | Invalid retail data |
| 5605 | `InvalidDevice` | Invalid device |
| 5606 | `InvalidAccountId` | Invalid account ID |
| 5607 | `InvalidCustomerData` | Invalid customer data |
| 5608 | `InvalidVerificationCode` | Invalid verification code |

### Installment Errors

| Code | Enum | Description |
|------|------|-------------|
| 5700 | `MissingAuthCommunication` | Missing successful authentication communication |
| 5701 | `RecommendationAlreadySent` | Recommendation already sent |
| 5702 | `MissingInstallmentData` | Missing installment data |
| 5703 | `InvalidOptionIndex` | Invalid option index |
| 5704 | `DomainSelectionNotAvailable` | Domain selection not available |
| 5705 | `FullAmountPaymentNotAvailable` | Full amount payment not available |
| 5706 | `AcquiringCallError` | Acquiring call error |
| 5707 | `InstallmentDataParseError` | Installment data parse error |
| 5708 | `InvalidInstallmentNumber` | Invalid installment number |
| 5709 | `MissingInstallmentOption` | Missing installment option |
| 5710 | `MissingRecommendationCommunication` | Missing successful recommendation communication |
| 5711 | `SettlementAccountIdRequired` | Settlement account ID required |
| 5712 | `SeriesSizeRequired` | Series size required |

### Card & API Version Errors

| Code | Enum | Description |
|------|------|-------------|
| 5812 | `InvalidCardType` | Invalid card type |
| 5813 | `CardTransactionRejected` | Card / transaction rejected |
| 5814 | `ApiV1Disabled` | API v1 is disabled |

### Mobile Application Errors

| Code | Enum | Description |
|------|------|-------------|
| 5900 | `InvalidAppPlatform` | Invalid application platform |
| 5901 | `InvalidTimestampApp` | Invalid timestamp |
| 5902 | `MobileAppNotFound` | Mobile application not found |
| 5903 | `DeeplinkDataNotFound` | Deeplink data package not found |
| 5904 | `PlatformOrInternalIdsRequired` | Platform or internal IDs required |
| 5905 | `ReturnUrlsRequired` | Return URLs required |
| 5906 | `MultipleReturnUrls` | Multiple return URLs provided |

## RTP Errors (6xxx)

| Code | Enum | Description |
|------|------|-------------|
| 6100 | `RtpNotEnabled` | RTP not enabled |
| 6101 | `RtpInvalidBatchRef` | Invalid batch reference |
| 6102 | `RtpInvalidOrderRef` | Invalid order reference |
| 6103 | `RtpInvalidTotal` | Invalid total amount |
| 6104 | `RtpInvalidCurrency` | Invalid currency |
| 6105 | `RtpCustomerRequired` | Customer required |
| 6106 | `RtpEmailOrAccountRequired` | Customer email or bank account required |
| 6107 | `RtpInvalidEmail` | Invalid customer email |
| 6108 | `RtpInvalidAccountNumber` | Invalid customer bank account number |
| 6109 | `RtpInvalidRecurringTimes` | Invalid recurring times |
| 6110 | `RtpRecurringUntilRequired` | Recurring until required |
| 6111 | `RtpRecurringUntilExceeded` | Recurring until exceeded |
| 6112 | `RtpInvalidRecurringDay` | Invalid recurring day |
| 6113 | `RtpInvalidRecurringInterval` | Invalid recurring interval |
| 6114 | `RtpEmptyPayments` | Empty payments |
| 6115 | `RtpInvalidPaymentResult` | Invalid payment result |
| 6116 | `RtpInvalidMerchant` | Invalid merchant |
| 6117 | `RtpSdkVersionRequired` | SDK version required |
| 6118 | `RtpTransactionAlreadyExists` | Transaction already exists |
| 6119 | `RtpInvalidLanguage` | Invalid language |
| 6120 | `RtpInvalidTransactionState` | Invalid transaction state |
| 6121 | `RtpInvalidBatchOrOrderRef` | Invalid batch or order reference |
| 6122 | `RtpInvalidQueryParams` | Invalid query parameters |
| 6123 | `RtpOrderIdSizeExceeded` | Order ID size exceeded |
| 6124 | `RtpFromAfterUntil` | From date is after until date |
| 6125 | `RtpFromAndUntilRequired` | From and until dates required together |
| 6126 | `RtpMissingRtpId` | Missing RTP ID |
| 6127 | `RtpInvalidState` | Invalid state |
| 6128 | `RtpInvalidDeadline` | Invalid deadline |
| 6129 | `RtpOrderRefOrTransactionIdRequired` | Order reference or transaction ID required for query |
| 6130 | `RtpGiroError` | Giro error |
| 6131 | `RtpTransactionNotFound` | Transaction does not exist |
| 6132 | `RtpCustomerOrAccountRequired` | Customer or bank account required |
| 6133 | `RtpCommentTooLong` | Comment too long |
| 6134 | `RtpAccountAlreadySet` | Bank account already set |
| 6135 | `RtpTransactionCountExceeded` | Transaction count exceeded |
| 6136 | `RtpEmptyPaymentAmount` | Empty payment amount for RTP |

### RTP Statuses

| Code | Enum | Description |
|------|------|-------------|
| 6140 | `RtpTransactionInit` | Transaction initialization |
| 6141 | `RtpWaitingForAccountData` | Waiting for bank account data |
| 6142 | `RtpReadyToSend` | Transaction ready to send |
| 6143 | `RtpTransactionSent` | Transaction sent |
| 6144 | `RtpTransactionReceived` | Transaction received |
| 6145 | `RtpTransactionAccepted` | Transaction accepted |
| 6146 | `RtpTransactionRejected` | Transaction rejected |
| 6147 | `RtpTransactionExpired` | Transaction expired |
| 6148 | `RtpTransactionReversed` | Transaction reversed |
| 6149 | `RtpTransactionFailed` | Transaction failed |
| 6150 | `RtpTransactionImmutable` | Immutable transaction |
| 6151 | `RtpTransactionPaid` | Transaction paid |
| 6153 | `RtpTransactionNotFound2` | Transaction not found |
| 6154 | `RtpTransactionFinalState` | Transaction in final state |
| 6155 | `RtpInvalidPartnerAccount` | Invalid partner bank account |
| 6156 | `RtpReversalInProgress` | Reversal in progress |
| 6157 | `RtpTransactionsRequired` | Transactions required |
| 6999 | `RtpGeneralError` | General RTP error |

## Settlement Errors (7xxx)

| Code | Enum | Description |
|------|------|-------------|
| 7100 | `SettlementMerchantNotFound` | Merchant not found |
| 7999 | `SettlementGeneralError` | General error |
