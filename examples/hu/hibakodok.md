# SimplePay hibakódok

A SimplePay API által visszaadott hibakódok teljes listája. A hibakódokat a `SimplePayApiException` kivétel tartalmazza, az `ErrorCode` enum segítségével programozottan is kezelhetők.

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

## Általános hibák

| Kód | Enum | Leírás |
|-----|------|--------|
| 999 | `GeneralError` | Általános hibakód |
| 1529 | `InternalError` | SimplePay belső hiba |
| 2999 | `InternalError2` | SimplePay belső hiba |

## Fizetési és kártyahibák (2xxx)

| Kód | Enum | Leírás |
|-----|------|--------|
| 2003 | `InvalidPassword` | Megadott jelszó érvénytelen |
| 2004 | `GeneralError2` | Általános hibakód |
| 2006 | `MerchantNotFound` | Megadott kereskedő nem található |
| 2008 | `InvalidEmail` | Megadott e-mail nem megfelelő |
| 2010 | `InvalidTransactionId` | Megadott tranzakcióazonosító nem megfelelő |
| 2013 | `InsufficientFunds` | Nincs elég fedezet a kártyán |
| 2014 | `PasswordRequired` | Fizetéshez jelszó szükséges |
| 2016 | `PaymentCancelledByUser` | A felhasználó megszakította a fizetést |
| 2019 | `AcquirerTimeout` | Időtúllépés az elfogadói kommunikációban |
| 2020 | `AcquirerError` | Elfogadó bank oldali hiba |
| 2021 | `Interactive3dsRequired` | Kártyakibocsátó interaktív 3DS ellenőrzést igényel |
| 2023 | `Repeated3dsFailed` | A megismételt 3DS ellenőrzés is sikertelen |
| 2030 | `CardNotDeletableOrInvalidAmount` | Kártya nem törölhető, mert egyenlege pozitív / Megadott összeg helytelen |
| 2040 | `InvalidCurrency` | Érvénytelen devizanem |
| 2063 | `CardInactive` | Kártya inaktív |
| 2064 | `InvalidCardData` | Hibás bankkártya adatok |
| 2066 | `CardNotChargeableOrLimitExceeded` | Kártya nem terhelhető / limittúllépés miatt |
| 2068 | `CardDataErrorOrNotExist` | Kártya adat hiba / nem létező kártya |
| 2071 | `InvalidCardDataOrNotExist` | Hibás bankkártya adatok / nem létező kártya |
| 2072 | `InvalidCardDataCheckRequired` | Hibásan megadott bankkártya adatok, ellenőrzés szükséges |
| 2073 | `InvalidCardDataCheckRequired2` | Hibásan megadott bankkártya adatok, ellenőrzés szükséges |
| 2074 | `CardholderNameTooLong` | Kártyabirtokos neve több, mint 32 karakter |
| 2077 | `InvalidCvc` | Érvénytelen CVC |
| 2078 | `GeneralCardError` | Általános hiba, a kártyakibocsátó bank nem adja meg a hiba okát |
| 2079 | `AcquirerNotAvailable` | A routingnak megfelelő elfogadó bank nem érhető el |
| 2130 | `InvalidInputCode` | Helytelen bemeneti kód |
| 2131 | `InvalidAddress` | Helytelen cím megadás |
| 2132 | `InvalidBirthDate` | Helytelen születési dátum |

## 3DS hibák (3xxx)

| Kód | Enum | Leírás |
|-----|------|--------|
| 3000 | `General3dsError` | Általános 3DS hiba |
| 3001 | `Invalid3dsResponse` | Érvénytelen 3DS válaszüzenet |
| 3002 | `ThreeDsProcessError` | 3DS folyamat hiba |
| 3003 | `ThreeDsProcessError2` | 3DS folyamat hiba |
| 3004 | `ThreeDsChallengeRedirect` | Redirect 3DS challenge folyamán |
| 3005 | `ThreeDsInteractiveAuthRequired` | 3DS interaktív azonosítás szükséges |
| 3006 | `ThreeDsCardAssociationTimeout` | 3DS folyamat hiba - Kártyatársasági válasz nem, vagy késve érkezik meg |
| 3012 | `ThreeDsNotCapableCard` | 3DS folyamat hiba, nem 3DS képes kártya |
| 3013 | `ThreeDsProcessError3` | 3DS folyamat hiba |
| 3102 | `Invalid3dsVersion` | Érvénytelen 3DS verzió |
| 3203 | `ThreeDsDataFormatError` | 3DS adat formátum hiba, vagy szükséges adat hiánya |
| 3303 | `ThreeDsInternalError` | 3DS belső hiba, érvénytelen 3DS végpont |
| 3402 | `ThreeDsVerificationTimeout` | 3DS ellenőrzési folyamat időtúllépés |
| 3403 | `ThreeDsTemporaryError` | 3DS átmeneti rendszerhiba |
| 3404 | `ThreeDsPermanentError` | 3DS tartósan fennálló rendszerhiba |
| 3501 | `ThreeDsAcsCancelledByUser` | 3DS ACS - a felhasználó megszakította az interaktív azonosítást |
| 3504 | `ThreeDsAcsTimeout` | 3DS ACS timeout, kártyakibocsátó bank oldal |
| 3505 | `ThreeDsAcsNoResponse` | 3DS ACS timeout, kártyakibocsátó bank oldalról nem érkezik válasz |
| 3506 | `ThreeDsAcsTransactionError` | 3DS ACS tranzakció hiba, kártyakibocsátó bank oldali |
| 3507 | `ThreeDsAcsUnknownError` | 3DS ACS ismeretlen hiba, kártyakibocsátó bank oldal |
| 3508 | `ThreeDsAcsComponentError` | 3DS ACS tranzakció hiba, kártyakibocsátó bank oldali komponensben |

## API hibák (5xxx)

### Általános API hibák

| Kód | Enum | Leírás |
|-----|------|--------|
| 5000 | `ApiGeneralError` | Általános hibakód |
| 5010 | `MerchantAccountNotFound` | A kereskedői fiók nem található |
| 5011 | `TransactionNotFound` | A tranzakció nem található |
| 5012 | `MerchantAccountMismatch` | A kereskedői fiók nem egyezik meg |
| 5013 | `TransactionAlreadyExists` | A tranzakció már létezik |
| 5014 | `InvalidTransactionType` | A tranzakció nem megfelelő típusú |
| 5015 | `TransactionInPayment` | A tranzakció éppen fizetés alatt |
| 5016 | `TransactionTimeout` | Tranzakció időtúllépés |
| 5017 | `TransactionAborted` | A tranzakció meg lett szakítva |
| 5018 | `TransactionAlreadyPaid` | A tranzakció már kifizetésre került |
| 5019 | `InvalidTransactionIdentifier` | Érvénytelen tranzakcióazonosító |
| 5020 | `OriginalTotalCheckFailed` | Az eredeti tranzakcióösszeg ellenőrzése sikertelen |
| 5021 | `TransactionAlreadyFinished` | A tranzakció már lezárásra került |
| 5022 | `TransactionInvalidState` | A tranzakció nem a kéréshez elvárt állapotban van |
| 5023 | `UnknownCurrency` | Ismeretlen / nem megfelelő fiók devizanem |
| 5024 | `InvalidEmailAddress` | Érvénytelen e-mail cím |
| 5025 | `InvalidName` | Érvénytelen név |
| 5026 | `TransactionBlockedByFraud` | Tranzakció letiltva (sikertelen fraud-vizsgálat) |
| 5027 | `TransactionLockError` | Tranzakció zárolási probléma |
| 5028 | `TransactionNotReconciled` | A tranzakció nem egyeztetett |
| 5029 | `TransactionNotRefunded` | A tranzakció még nem került visszatérítésre |
| 5030 | `OperationNotAllowed` | A művelet nem engedélyezett |
| 5033 | `RefundCancelNotAllowed` | Visszatérítési tranzakció törlése nem engedélyezett |

### Tárolt kártya és recurring hibák

| Kód | Enum | Leírás |
|-----|------|--------|
| 5040 | `StoredCardNotFound` | Tárolt kártya nem található |
| 5041 | `StoredCardExpired` | Tárolt kártya lejárt |
| 5042 | `StoredCardDeactivated` | Tárolt kártya inaktiválva |
| 5043 | `PhantomStoredCard` | Fantom tárolt kártya |
| 5044 | `RecurringNotEnabled` | Recurring nincs engedélyezve |
| 5045 | `RecurringRemoteIdRequired` | Ismétlődő regisztrációhoz távoli azonosító szükséges |
| 5046 | `RecurringIdtsRequired` | Ismétlődő regisztrációhoz IDTS szükséges |
| 5047 | `RecurringDuplicateIdts` | Ismétlődő IDTS |
| 5048 | `RecurringUntilRequired` | Recurring until szükséges |
| 5049 | `RecurringUntilMismatch` | Recurring until eltér |
| 5071 | `StoredCardInvalidLength` | Tárolt kártya érvénytelen hossz |
| 5072 | `StoredCardInvalidOperation` | Tárolt kártya érvénytelen művelet |
| 5080 | `OriginalIdMismatchOnRecurring` | Eredeti azonosító eltérés az ismétlődő regisztrációnál |
| 5081 | `RecurringTokenNotFound` | Recurring token nem található |
| 5082 | `RecurringTokenInUse` | Recurring token használatban |
| 5083 | `TokenTimesRequired` | Token times szükséges |
| 5084 | `TokenTimesTooLarge` | Token times túl nagy |
| 5085 | `TokenUntilRequired` | Token until szükséges |
| 5086 | `TokenUntilTooLarge` | Token until túl nagy |
| 5087 | `TokenMaxAmountRequired` | Token maxAmount szükséges |
| 5088 | `TokenMaxAmountTooLarge` | Token maxAmount túl nagy |
| 5089 | `RecurringAndOneclickConflict` | Recurring és oneclick regisztráció egyszerre nem indítható |
| 5090 | `RecurringTokenRequired` | Recurring token szükséges |
| 5091 | `RecurringTokenInactive` | Recurring token inaktív |
| 5092 | `RecurringTokenExpired` | Recurring token lejárt |
| 5093 | `RecurringAccountMismatch` | Recurring account eltérés |
| 5094 | `InvalidTimeoutDefinition` | Érvénytelen időtúllépés meghatározás |
| 5095 | `PreselectedMethodRequired` | Előre kiválasztott fizetési mód szükséges |
| 5096 | `EamNotEnabled` | Az EAM használata nem engedélyezett |
| 5097 | `InvalidEamType` | Érvénytelen EAM típus |

### Validációs hibák

| Kód | Enum | Leírás |
|-----|------|--------|
| 5050 | `InvalidPaymentMethod` | Érvénytelen fizetési módszer |
| 5051 | `InvalidReferenceNumber` | Érvénytelen referencia szám |
| 5052 | `InvalidExternalReference` | Érvénytelen külső referencia |
| 5053 | `InvalidTimestamp` | Érvénytelen időbélyeg |
| 5054 | `InvalidBillingFirstName` | Érvénytelen számlázási keresztnév |
| 5055 | `InvalidBillingLastName` | Érvénytelen számlázási vezetéknév |
| 5056 | `InvalidBillingEmail` | Érvénytelen számlázási e-mail cím |
| 5057 | `InvalidBillingPhone` | Érvénytelen számlázási telefonszám |
| 5058 | `InvalidBillingAddress` | Érvénytelen számlázási cím |
| 5059 | `InvalidBillingCity` | Érvénytelen számlázási város |
| 5060 | `InvalidShippingFirstName` | Érvénytelen szállítási keresztnév |
| 5061 | `InvalidShippingLastName` | Érvénytelen szállítási vezetéknév |
| 5062 | `InvalidShippingPhone` | Érvénytelen szállítási telefonszám |
| 5063 | `InvalidShippingAddress` | Érvénytelen szállítási cím |
| 5064 | `InvalidShippingCity` | Érvénytelen szállítási város |
| 5065 | `InvalidAmount` | Érvénytelen összeg |
| 5066 | `InvalidCurrencyCode` | Érvénytelen pénznem |
| 5067 | `MissingBillingEmail` | Kötelező számlázási e-mail cím hiányzik |
| 5068 | `InvalidShippingEmail` | Érvénytelen szállítási e-mail cím |
| 5069 | `MaxAmountExceeded` | Elérte a maximálisan megengedett összeget |
| 5070 | `DirectPaymentCreationError` | Hiba az új közvetlen fizetés létrehozásakor |

### Aláírás és kérés hibák

| Kód | Enum | Leírás |
|-----|------|--------|
| 5100 | `InvalidSignature` | Érvénytelen aláírás |
| 5101 | `MissingOrderRef` | Hiányzó rendelési hivatkozás |
| 5102 | `MissingOrderAmount` | Hiányzó rendelési összeg |
| 5103 | `MissingOrderCurrency` | Hiányzó rendelési pénznem |
| 5104 | `MissingDate` | Hiányzó dátum |
| 5105 | `ConfirmationError` | Hiba a megerősítés során |
| 5106 | `InvalidRefundAmount` | Érvénytelen visszatérítési összeg |
| 5110 | `InvalidRefundTotal` | Nem megfelelő visszatérítendő összeg |
| 5111 | `OrderRefOrTransactionIdRequired` | Az orderRef és a transactionId közül az egyik küldése kötelező |
| 5113 | `SdkVersionRequired` | Az sdkVersion kötelező |
| 5150 | `MissingIosMerchantId` | Hiányzó iOS kereskedő azonosító |
| 5151 | `MissingExternalReference` | Hiányzó külső referencia |
| 5199 | `InvalidCardDetails` | Érvénytelen kártyaadatok |

### Számla és termék hibák

| Kód | Enum | Leírás |
|-----|------|--------|
| 5200 | `InvalidInvoice` | Érvénytelen számla |
| 5201 | `MissingMerchantId` | A kereskedői fiók azonosítója hiányzik |
| 5202 | `AccessDenied` | Hozzáférés megtagadva |
| 5203 | `InvalidData` | Érvénytelen adat |
| 5204 | `InvalidProductCode` | Érvénytelen termékkód |
| 5205 | `InvalidProductName` | Érvénytelen terméknév |
| 5206 | `InvalidProductInfo` | Érvénytelen termékinformáció |
| 5207 | `InvalidProductPrice` | Érvénytelen termékár |
| 5208 | `InvalidVatValue` | Érvénytelen ÁFA érték |
| 5209 | `InvalidQuantity` | Érvénytelen mennyiség |
| 5210 | `InvalidProductVariant` | Érvénytelen termékváltozat |
| 5211 | `InvalidProductGroup` | Érvénytelen termékcsoport |
| 5212 | `InvalidPrice` | Érvénytelen ár |
| 5213 | `MissingOrderRefField` | A kereskedői tranzakcióazonosító hiányzik |
| 5214 | `InvalidDate` | Érvénytelen dátum |
| 5215 | `InvalidDiscount` | Érvénytelen kedvezmény |
| 5216 | `InvalidShippingAmount` | Érvénytelen szállítási összeg |
| 5217 | `InvalidMethod` | Érvénytelen módszer |
| 5218 | `InvalidRedirectUrl` | Érvénytelen visszairányítási URL |
| 5219 | `MissingOrInvalidCustomerEmail` | Email cím hiányzik, vagy nem email formátumú |
| 5220 | `InvalidLanguage` | A tranzakció nyelve nem megfelelő |
| 5221 | `InvalidDiscountFormat` | Érvénytelen kedvezmény formátum |
| 5222 | `InvalidShippingFormat` | Érvénytelen szállítási formátum |
| 5223 | `InvalidOrMissingCurrency` | A tranzakció pénzneme nem megfelelő, vagy hiányzik |
| 5224 | `InvalidTimeLimit` | Érvénytelen időkorlát |
| 5225 | `InvalidWireTransferTimeLimit` | Érvénytelen banki átutalási időkorlát |
| 5226 | `InvalidCommunicationState` | Érvénytelen kommunikációs állapot |
| 5227 | `InvalidTimeoutSpec` | Érvénytelen időtúllépés meghatározás |
| 5228 | `RefundLimitExceeded` | Visszatérítési limit túllépve |
| 5229 | `RefundDeadlineExpired` | Visszatérítési határidő lejárt |
| 5230 | `RefundNotAllowed` | Visszatérítés nem engedélyezett |

### Tranzakciókezelési hibák

| Kód | Enum | Leírás |
|-----|------|--------|
| 5301 | `UserRedirectRequired` | Felhasználó átirányítása szükséges |
| 5302 | `InvalidRequestSignature` | Nem megfelelő aláírás a beérkező kérésben |
| 5303 | `InvalidRequestSignature2` | Nem megfelelő aláírás a beérkező kérésben |
| 5304 | `TimeoutFailedCall` | Időtúllépés miatt sikertelen hívás |
| 5305 | `FailedTransactionSend` | Sikertelen tranzakcióküldés az elfogadó felé |
| 5306 | `FailedTransactionCreation` | Sikertelen tranzakciólétrehozás |
| 5307 | `CurrencyMismatch` | A kérésben megadott devizanem nem egyezik a fiókhoz beállítottal |
| 5308 | `TwoStepNotEnabled` | A kétlépcsős tranzakcióindítás nem engedélyezett a kereskedői fiókon |
| 5309 | `MissingInvoiceName` | Számlázási adatokban a címzett hiányzik |
| 5310 | `MissingInvoiceCity` | Számlázási adatokban a város kötelező |
| 5311 | `MissingInvoiceZip` | Számlázási adatokban az irányítószám kötelező |
| 5312 | `MissingInvoiceAddress` | Számlázási adatokban a cím első sora kötelező |
| 5313 | `MissingItemTitle` | A termék neve kötelező |
| 5314 | `MissingItemPrice` | A termék egységára kötelező |
| 5315 | `MissingItemQuantity` | A rendelt mennyiség kötelező |
| 5316 | `MissingShippingName` | Szállítási adatokban a címzett kötelező |
| 5317 | `MissingShippingCity` | Szállítási adatokban a város kötelező |
| 5318 | `MissingShippingZip` | Szállítási adatokban az irányítószám kötelező |
| 5319 | `MissingShippingAddress` | Szállítási adatokban a cím első sora kötelező |
| 5320 | `MissingSdkVersion` | Az sdkVersion kötelező |
| 5321 | `InvalidJsonFormat` | Formátumhiba / érvénytelen JSON string |
| 5322 | `InvalidCountry` | Érvénytelen ország |
| 5323 | `InvalidFinishAmount` | Lezárás összege érvénytelen |
| 5324 | `ItemsOrTotalRequired` | Termékek listája vagy tranzakcióföösszeg szükséges |
| 5325 | `InvalidUrl` | Érvénytelen URL |
| 5326 | `MissingCardId` | Hiányzó cardId |
| 5327 | `MaxOrderRefsExceeded` | Lekérdezendő orderRef-ek maximális számának túllépése |
| 5328 | `MaxTransactionIdsExceeded` | Lekérdezendő tranzakcióazonosítók maximális számának túllépése |
| 5329 | `QueryFromAfterUntil` | Lekérdezésben a from az until időpontot meg kell előzze |
| 5330 | `QueryFromAndUntilRequired` | Lekérdezésben from és until együttesen adandó meg |
| 5331 | `InvalidTransactionSource` | Érvénytelen tranzakció forrás |
| 5332 | `TransactionIdAndTargetRequired` | Tranzakcióazonosító és célpont szükséges |
| 5333 | `MissingTransactionId` | Hiányzó tranzakcióazonosító |
| 5334 | `CardDataRequired` | Bankkártya adatok szükségesek |
| 5335 | `CardBinRequired` | Bankkártya BIN szükséges |
| 5336 | `AntiFraudHashRequired` | Csalásellenes hash szükséges |
| 5337 | `DataSerializationError` | Hiba összetett adat szöveges formába írásakor |
| 5338 | `AcquirerTransactionIdRequired` | Acquirer tranzakcióazonosítója szükséges |
| 5339 | `QueryDateOrIdsRequired` | Lekérdezéshez az indítás időszaka vagy azonosítók szükségesek |
| 5340 | `TransactionNotWalletBound` | A tranzakció nem tárcához kötött |
| 5341 | `PartnerOptionRequired` | Partner opció szükséges |
| 5342 | `PartnerAccountsRequired` | Partner számlák szükségesek |
| 5343 | `InvalidTransactionStatus` | Nem megfelelő tranzakcióstátusz |
| 5344 | `InvalidTransactionStatus2` | Nem megfelelő tranzakcióstátusz |
| 5345 | `VatAmountBelowZero` | ÁFA összege kisebb, mint 0 |
| 5346 | `InvalidTransactionMode` | Érvénytelen tranzakciós mód |
| 5347 | `InvalidInstallmentPlan` | Érvénytelen részletfizetés |
| 5348 | `JwtRequired` | JWT szükséges |
| 5349 | `TransactionNotAllowedOnAccount` | A tranzakció nem engedélyezett az elszámoló fiókon |
| 5350 | `InvalidEmailFormat` | Érvénytelen email |
| 5351 | `InvalidDay` | Érvénytelen nap |
| 5352 | `SimpleBusinessAccountError` | Simple business fiók hiba / nem létező fiók |
| 5353 | `InvalidStartEndParams` | Érvénytelen kezdő és záró paraméterek |
| 5354 | `InvalidDeviceId` | Érvénytelen eszközazonosító |
| 5355 | `NotSoftPosAccount` | Nem SoftPOS számla |
| 5356 | `InvalidTemplate` | Érvénytelen sablon |
| 5357 | `InvalidOrderRefFormat` | Érvénytelen rendelési hivatkozás |
| 5358 | `InvalidPeriod` | Érvénytelen időszak |
| 5359 | `SoftPosNotAvailable` | SoftPOS nem elérhető |
| 5360 | `RemoteAssignmentFailed` | A tranzakció távoli hozzárendelése sikertelen |
| 5361 | `RemoteDeletionFailed` | A tranzakció távoli törlése sikertelen |
| 5362 | `RemoteExpirationFailed` | A tranzakció távoli lejáratának beállítása sikertelen |
| 5363 | `OriginalTransactionRequired` | Eredeti tranzakció szükséges |
| 5364 | `InvalidAdditionalInfo` | Érvénytelen további információ |
| 5365 | `PartnerIdRequired` | Partner azonosító megadása kötelező |
| 5366 | `InvalidEamStatus` | Érvénytelen EAM állapot |
| 5367 | `PaymentReferenceRequired` | Fizetési hivatkozás megadása kötelező |
| 5368 | `DebtorBicRequired` | Adós bankjának BIC kódja szükséges |
| 5369 | `EndToEndIdRequired` | End-to-end azonosító megadása kötelező |
| 5370 | `InvalidItemPrice` | Érvénytelen termékár |
| 5371 | `InvalidItemQuantity` | Érvénytelen termékmennyiség |
| 5372 | `AmountPaidToMerchant` | Összeg kifizetve a kereskedőnek |
| 5373 | `ApplePayNotEnabled` | Apple Pay nem engedélyezett |
| 5380 | `EmailParamsRequired` | E-mail paraméterek szükségesek |
| 5381 | `EmailTypeRequired` | E-mail típus szükséges |
| 5382 | `CustomerEmailRequired` | Ügyfél e-mail címe szükséges |

### Biztonsági és munkamenet hibák

| Kód | Enum | Leírás |
|-----|------|--------|
| 5401 | `InvalidSaltLength` | Érvénytelen salt, nem 32-64 hosszú |
| 5402 | `TransactionBaseRequired` | Tranzakció alapja szükséges |
| 5403 | `SimpleTransactionInPayment` | Simple tranzakció a fizetés alatt |
| 5404 | `InvalidTransactionState` | Érvénytelen tranzakció állapot |
| 5405 | `TransactionNotInCdeSession` | A tranzakció nem CDE munkamenetben van |
| 5413 | `WireTransactionCreated` | Létrejött utalási tranzakció |

### Böngésző adatok (3DS)

| Kód | Enum | Leírás |
|-----|------|--------|
| 5501 | `BrowserAcceptRequired` | Böngésző accept kötelező |
| 5502 | `BrowserAgentRequired` | Böngésző agent kötelező |
| 5503 | `BrowserIpRequired` | Böngésző ip kötelező |
| 5504 | `BrowserJavaRequired` | Böngésző java kötelező |
| 5505 | `BrowserLanguageRequired` | Böngésző nyelv kötelező |
| 5506 | `BrowserColorRequired` | Böngésző szín kötelező |
| 5507 | `BrowserHeightRequired` | Böngésző magasság kötelező |
| 5508 | `BrowserWidthRequired` | Böngésző szélesség kötelező |
| 5509 | `BrowserTzRequired` | Böngésző tz kötelező |
| 5530 | `InvalidType` | Érvénytelen type |
| 5555 | `RavenCallError` | Hiba a Raven hívás során |

### SoftPOS és eszköz hibák

| Kód | Enum | Leírás |
|-----|------|--------|
| 5601 | `InvalidIdentificationCode` | Érvénytelen azonosító kód |
| 5602 | `InvalidMessage` | Érvénytelen üzenet |
| 5603 | `InvalidTransactionSituation` | Érvénytelen tranzakciós helyzet |
| 5604 | `InvalidRetailData` | Érvénytelen kiskereskedelmi adatok |
| 5605 | `InvalidDevice` | Érvénytelen eszköz |
| 5606 | `InvalidAccountId` | Érvénytelen számlaazonosító |
| 5607 | `InvalidCustomerData` | Érvénytelen ügyfél adatok |
| 5608 | `InvalidVerificationCode` | Érvénytelen ellenőrző kód |

### Részletfizetési hibák

| Kód | Enum | Leírás |
|-----|------|--------|
| 5700 | `MissingAuthCommunication` | Hiányzó sikeres hitelesítési kommunikáció |
| 5701 | `RecommendationAlreadySent` | Ajánlás már elküldve |
| 5702 | `MissingInstallmentData` | Hiányzó részletfizetési adat |
| 5703 | `InvalidOptionIndex` | Érvénytelen opció index |
| 5704 | `DomainSelectionNotAvailable` | Tartomány választás nem elérhető |
| 5705 | `FullAmountPaymentNotAvailable` | Teljes összegű fizetés nem elérhető |
| 5706 | `AcquiringCallError` | Hiba az acquiring hívás során |
| 5707 | `InstallmentDataParseError` | Részletfizetési adat értelmezési hiba |
| 5708 | `InvalidInstallmentNumber` | Érvénytelen részletfizetési szám |
| 5709 | `MissingInstallmentOption` | Hiányzó részletfizetési opció |
| 5710 | `MissingRecommendationCommunication` | Hiányzó sikeres ajánlási kommunikáció |
| 5711 | `SettlementAccountIdRequired` | Elszámolószámla-azonosító szükséges |
| 5712 | `SeriesSizeRequired` | Sorozat méret szükséges |

### Kártya és API verzió hibák

| Kód | Enum | Leírás |
|-----|------|--------|
| 5812 | `InvalidCardType` | Érvénytelen kártyatípus |
| 5813 | `CardTransactionRejected` | Kártya / tranzakció elutasítva |
| 5814 | `ApiV1Disabled` | API v1 verziója le van tiltva |

### Mobil alkalmazás hibák

| Kód | Enum | Leírás |
|-----|------|--------|
| 5900 | `InvalidAppPlatform` | Érvénytelen alkalmazás platform |
| 5901 | `InvalidTimestampApp` | Érvénytelen időbélyeg |
| 5902 | `MobileAppNotFound` | Mobil alkalmazás nem található |
| 5903 | `DeeplinkDataNotFound` | Deeplink adatcsomag nem található |
| 5904 | `PlatformOrInternalIdsRequired` | Platform vagy belső azonosítók szükségesek |
| 5905 | `ReturnUrlsRequired` | Visszatérő URL-ek szükségesek |
| 5906 | `MultipleReturnUrls` | Több visszatérő URL megadva |

## RTP hibák (6xxx)

| Kód | Enum | Leírás |
|-----|------|--------|
| 6100 | `RtpNotEnabled` | RTP nem engedélyezett |
| 6101 | `RtpInvalidBatchRef` | Érvénytelen csomag hivatkozás |
| 6102 | `RtpInvalidOrderRef` | Érvénytelen rendelési hivatkozás |
| 6103 | `RtpInvalidTotal` | Érvénytelen teljes összeg |
| 6104 | `RtpInvalidCurrency` | Érvénytelen pénznem |
| 6105 | `RtpCustomerRequired` | Ügyfél szükséges |
| 6106 | `RtpEmailOrAccountRequired` | Szükséges az ügyfél e-mail címe vagy bankszámlaszáma |
| 6107 | `RtpInvalidEmail` | Érvénytelen ügyfél e-mail cím |
| 6108 | `RtpInvalidAccountNumber` | Érvénytelen ügyfél bankszámlaszám |
| 6109 | `RtpInvalidRecurringTimes` | Érvénytelen ismétlődő idők |
| 6110 | `RtpRecurringUntilRequired` | Szükséges az ismétlődés időtartama |
| 6111 | `RtpRecurringUntilExceeded` | Az ismétlődés időtartama túllépve |
| 6112 | `RtpInvalidRecurringDay` | Érvénytelen ismétlődő nap |
| 6113 | `RtpInvalidRecurringInterval` | Érvénytelen ismétlődő intervallum |
| 6114 | `RtpEmptyPayments` | Üres fizetések |
| 6115 | `RtpInvalidPaymentResult` | Érvénytelen fizetési eredmény |
| 6116 | `RtpInvalidMerchant` | Érvénytelen kereskedő |
| 6117 | `RtpSdkVersionRequired` | Szükséges az SDK verziója |
| 6118 | `RtpTransactionAlreadyExists` | A tranzakció már létezik |
| 6119 | `RtpInvalidLanguage` | Érvénytelen nyelv |
| 6120 | `RtpInvalidTransactionState` | Érvénytelen tranzakciós állapot |
| 6121 | `RtpInvalidBatchOrOrderRef` | Érvénytelen csomag vagy rendelési hivatkozás |
| 6122 | `RtpInvalidQueryParams` | Érvénytelen lekérdezési paraméterek |
| 6123 | `RtpOrderIdSizeExceeded` | Rendelési azonosító méret túllépés |
| 6124 | `RtpFromAfterUntil` | A kezdő dátum a záró dátum után van |
| 6125 | `RtpFromAndUntilRequired` | Kezdő és záró dátum együtt szükséges |
| 6126 | `RtpMissingRtpId` | Hiányzó RTP azonosító |
| 6127 | `RtpInvalidState` | Érvénytelen állapot |
| 6128 | `RtpInvalidDeadline` | Érvénytelen határidő |
| 6129 | `RtpOrderRefOrTransactionIdRequired` | Lekérdezéshez rendelési hivatkozás vagy tranzakcióazonosító szükséges |
| 6130 | `RtpGiroError` | Giro hiba |
| 6131 | `RtpTransactionNotFound` | A tranzakció nem létezik |
| 6132 | `RtpCustomerOrAccountRequired` | Ügyfél vagy bankszámla szükséges |
| 6133 | `RtpCommentTooLong` | Túl hosszú közlemény |
| 6134 | `RtpAccountAlreadySet` | A bankszámla már be van állítva |
| 6135 | `RtpTransactionCountExceeded` | Tranzakciók számának túllépése |
| 6136 | `RtpEmptyPaymentAmount` | Üres fizetendő összeg RTP-nél |

### RTP státuszok

| Kód | Enum | Leírás |
|-----|------|--------|
| 6140 | `RtpTransactionInit` | Tranzakció inicializálása |
| 6141 | `RtpWaitingForAccountData` | Várakozás a bankszámla adatokra |
| 6142 | `RtpReadyToSend` | A tranzakció elküldésre kész |
| 6143 | `RtpTransactionSent` | A tranzakció elküldve |
| 6144 | `RtpTransactionReceived` | A tranzakció fogadva |
| 6145 | `RtpTransactionAccepted` | A tranzakció elfogadva |
| 6146 | `RtpTransactionRejected` | A tranzakció elutasítva |
| 6147 | `RtpTransactionExpired` | A tranzakció lejárt |
| 6148 | `RtpTransactionReversed` | A tranzakció visszafordítva |
| 6149 | `RtpTransactionFailed` | Sikertelen tranzakció |
| 6150 | `RtpTransactionImmutable` | Változtathatatlan tranzakció |
| 6151 | `RtpTransactionPaid` | Fizetett tranzakció |
| 6153 | `RtpTransactionNotFound2` | Tranzakció nem található |
| 6154 | `RtpTransactionFinalState` | Tranzakció végállapotban |
| 6155 | `RtpInvalidPartnerAccount` | Érvénytelen partner bankszámla |
| 6156 | `RtpReversalInProgress` | Visszafordítás folyamatban |
| 6157 | `RtpTransactionsRequired` | Tranzakciók szükségesek |
| 6999 | `RtpGeneralError` | Általános RTP hiba |

## Elszámolási hibák (7xxx)

| Kód | Enum | Leírás |
|-----|------|--------|
| 7100 | `SettlementMerchantNotFound` | A kereskedő nem található |
| 7999 | `SettlementGeneralError` | Általános hiba |
