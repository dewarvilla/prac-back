<?php

namespace App\Exceptions;

class NotFoundException extends ApiException
{
    public function __construct(
        string $message = 'Recurso no encontrado.',
        string $errorCode = 'NOT_FOUND',
        array $details = []
    ) {
        parent::__construct($message, $errorCode, 404, $details);
    }
}
