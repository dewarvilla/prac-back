<?php

namespace App\Exceptions;

use RuntimeException;

abstract class ApiException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly string $errorCode,
        public readonly int $statusCode = 409,
        public readonly array $details = [],
        int $code = 0
    ) {
        parent::__construct($message, $code);
    }
}
