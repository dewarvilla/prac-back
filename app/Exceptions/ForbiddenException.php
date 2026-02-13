<?php

namespace App\Exceptions;

class ForbiddenException extends ApiException
{
    public function __construct(
        string $message = 'No tienes permisos para esta acción.',
        string $errorCode = 'FORBIDDEN',
        array $details = []
    ) {
        parent::__construct($message, $errorCode, 403, $details);
    }
}
