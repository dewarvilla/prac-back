<?php

namespace App\Exceptions;

class UnprocessableEntityException extends ApiException
{
    public function __construct(
        string $message = 'El recurso no está en un estado válido para esta acción.',
        string $errorCode = 'UNPROCESSABLE_ENTITY',
        array $details = []
    ) {
        parent::__construct($message, $errorCode, 422, $details);
    }
}
