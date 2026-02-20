<?php

namespace App\Exceptions\Creaciones;

use App\Exceptions\UnprocessableEntityException;

class CreacionNotEditableException extends UnprocessableEntityException
{
    public function __construct(string $estadoCreacion)
    {
        parent::__construct(
            'La creación de la práctica no se puede modificar en el estado actual.',
            'CREACION_NOT_EDITABLE',
            ['estado_creacion' => $estadoCreacion]
        );
    }
}
