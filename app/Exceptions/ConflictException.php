<?php

namespace App\Exceptions;

class ConflictException extends ApiException
{
    public function __construct(
        string $message = 'Conflicto con el estado actual del recurso.',
        string $errorCode = 'CONFLICT',
        array $details = []
    ) {
        parent::__construct($message, $errorCode, 409, $details);
    }
}
