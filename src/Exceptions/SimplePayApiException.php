<?php

namespace Netipar\SimplePay\Exceptions;

use Netipar\SimplePay\Enums\ErrorCode;
use RuntimeException;

class SimplePayApiException extends RuntimeException
{
    /** @var array<int, int> */
    private array $errorCodes;

    /** @var array<int, ErrorCode> */
    private array $resolvedCodes;

    /**
     * @param  array<int, int>  $errorCodes
     */
    public function __construct(array $errorCodes, array $responseData = [])
    {
        $this->errorCodes = $errorCodes;
        $this->resolvedCodes = array_filter(
            array_map(fn (int $code) => ErrorCode::tryFrom($code), $errorCodes),
        );

        $descriptions = array_map(function (int $code) {
            $enum = ErrorCode::tryFrom($code);

            return $enum
                ? "[{$code}] {$enum->description()}"
                : "[{$code}] Ismeretlen hibakód";
        }, $errorCodes);

        $message = implode('; ', $descriptions);

        parent::__construct($message);
    }

    /**
     * @return array<int, int>
     */
    public function getErrorCodes(): array
    {
        return $this->errorCodes;
    }

    /**
     * @return array<int, ErrorCode>
     */
    public function getResolvedCodes(): array
    {
        return $this->resolvedCodes;
    }

    public function hasErrorCode(int $code): bool
    {
        return in_array($code, $this->errorCodes, true);
    }
}
